<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\ConfirmSubscription;

use PHPUnit_Framework_MockObject_MockObject;
use WMDE\Fundraising\Entities\Donation as DoctrineDonation;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Model\MailAddress;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\Fixtures\SucceedingDonationAuthorizer;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;
use WMDE\Fundraising\Frontend\UseCases\CancelDonation\CancelDonationRequest;
use WMDE\Fundraising\Frontend\UseCases\CancelDonation\CancelDonationUseCase;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\CancelDonation\CancelDonationUseCase
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CancelDonationUseCaseTest extends \PHPUnit_Framework_TestCase {

	const CORRECT_UPDATE_TOKEN = 'b5b249c8beefb986faf8d186a3f16e86ef509ab2';

	public function testGivenIdOfUnknownDonation_cancellationIsNotSuccessful() {
		$useCase = $this->newUseCase();

		$response = $useCase->cancelDonation( new CancelDonationRequest( 1337 ) );

		$this->assertFalse( $response->cancellationWasSuccessful() );
	}

	private function newUseCase(): CancelDonationUseCase {
		return $this->newFactoryWithNullMailer()->newCancelDonationUseCase( self::CORRECT_UPDATE_TOKEN );
	}

	private function newFactoryWithNullMailer(): FunFunFactory {
		$factory = TestEnvironment::newInstance()->getFactory();

		$factory->setNullMessenger();

		return $factory;
	}

	public function testResponseContainsDonationId() {
		$useCase = $this->newUseCase();

		$response = $useCase->cancelDonation( new CancelDonationRequest( 1337 ) );

		$this->assertEquals( 1337, $response->getDonationId() );
	}

	public function testGivenIdOfCancellableDonation_cancellationIsSuccessful() {
		$factory = $this->newFactoryWithNullMailer();

		$donation = ValidDonation::newDonation();
		$donation->setStatus( Donation::STATUS_NEW );
		$donation->setPaymentType( PaymentType::DIRECT_DEBIT );

		$this->storeDonation( $donation, $factory );

		$useCase = $factory->newCancelDonationUseCase( self::CORRECT_UPDATE_TOKEN );
		$response = $useCase->cancelDonation( new CancelDonationRequest( $donation->getId() ) );

		$this->assertTrue( $response->cancellationWasSuccessful() );
	}

	// TODO: refactor once token generation is done by the repo
	private function storeDonation( Donation $donation, FunFunFactory $factory ) {
		$factory->getDonationRepository()->storeDonation( $donation );

		/**
		 * @var DoctrineDonation $doctrineDonation
		 */
		$doctrineDonation = $factory->getEntityManager()->getRepository( DoctrineDonation::class )->find( $donation->getId() );

		$donationData = $doctrineDonation->getDataObject();
		$donationData->setUpdateToken( self::CORRECT_UPDATE_TOKEN );
		$donationData->setUpdateTokenExpiry( date( 'Y-m-d H:i:s', time() + 60 * 60 ) );
		$doctrineDonation->setDataObject( $donationData );

		$factory->getEntityManager()->persist( $doctrineDonation );
		$factory->getEntityManager()->flush();
	}

	public function testGivenIdOfNonCancellableDonation_cancellationIsNotSuccessful() {
		$factory = $this->newFactoryWithNullMailer();

		$donation = ValidDonation::newDonation();
		$donation->setStatus( Donation::STATUS_DELETED );
		$donation->setPaymentType( PaymentType::DIRECT_DEBIT );

		$this->storeDonation( $donation, $factory );

		$useCase = $factory->newCancelDonationUseCase( self::CORRECT_UPDATE_TOKEN );
		$response = $useCase->cancelDonation( new CancelDonationRequest( $donation->getId() ) );

		$this->assertFalse( $response->cancellationWasSuccessful() );
	}

	public function testGivenValidRequest_cancellationConfirmationEmailIsSend() {
		$donation = $this->newCancelableDonation();

		$useCase = $this->newUseCaseWithMailerMock( $donation );

		$response = $useCase->cancelDonation( new CancelDonationRequest( $donation->getId() ) );

		$this->assertTrue( $response->cancellationWasSuccessful() );
	}

	private function newCancelableDonation(): Donation {
		$donation = ValidDonation::newDonation();

		$donation->setStatus( Donation::STATUS_NEW );
		$donation->setPaymentType( PaymentType::DIRECT_DEBIT );

		return $donation;
	}

	private function newUseCaseWithMailerMock( Donation $donation ): CancelDonationUseCase {
		$mailer = $this->newMailer();

		$mailer->expects( $this->once() )
			->method( 'sendMail' )
			->with(
				$this->equalTo( new MailAddress( $donation->getDonor()->getEmailAddress() ) ),
				$this->callback( function( $value ) {
					$this->assertInternalType( 'array', $value );
					$this->assertSame( 'Sehr geehrte Damen und Herren,', $value['salutation'] );
					$this->assertSame( 1, $value['donationId'] );
					return true;
				} )
			);

		return new CancelDonationUseCase(
			$this->getDonationRepositoryWithDonation( $donation ),
			$mailer,
			new SucceedingDonationAuthorizer()
		);
	}

	/**
	 * @return TemplateBasedMailer|PHPUnit_Framework_MockObject_MockObject
	 */
	private function newMailer(): TemplateBasedMailer {
		return $this->getMockBuilder( TemplateBasedMailer::class )
			->disableOriginalConstructor()
			->getMock();
	}

	private function getDonationRepositoryWithDonation( Donation $donation ): DonationRepository {
		$donationRepository = TestEnvironment::newInstance()->getFactory()->getDonationRepository();

		$donationRepository->storeDonation( $donation );

		return $donationRepository;
	}

}
