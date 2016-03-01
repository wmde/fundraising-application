<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\ConfirmSubscription;

use PHPUnit_Framework_MockObject_MockObject;
use Swift_NullTransport;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Model\MailAddress;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\Messenger;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;
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

	public function testGivenIdOfUnknownDonation_cancellationIsNotSuccessful() {
		$useCase = $this->newUseCase();

		$response = $useCase->cancelDonation( new CancelDonationRequest( 1337, 'token', 'updateToken' ) );

		$this->assertFalse( $response->cancellationWasSuccessful() );
	}

	private function newUseCase(): CancelDonationUseCase {
		return $this->newFactoryWithNullMailer()->newCancelDonationUseCase();
	}

	private function newFactoryWithNullMailer(): FunFunFactory {
		$factory = TestEnvironment::newInstance()->getFactory();

		$factory->setMessenger( new Messenger(
			Swift_NullTransport::newInstance(),
			$factory->getOperatorAddress()
		) );

		return $factory;
	}

	public function testResponseContainsDonationId() {
		$useCase = $this->newUseCase();

		$response = $useCase->cancelDonation( new CancelDonationRequest( 1337, 'token', 'updateToken' ) );

		$this->assertEquals( 1337, $response->getDonationId() );
	}

	public function testGivenIdOfCancellableDonation_cancellationIsSuccessful() {
		$factory = $this->newFactoryWithNullMailer();

		$donation = ValidDonation::newDonation();
		$donation->setStatus( Donation::STATUS_NEW );
		$donation->setPaymentType( PaymentType::DIRECT_DEBIT );

		$factory->getDonationRepository()->storeDonation( $donation );

		$useCase = $factory->newCancelDonationUseCase();
		$response = $useCase->cancelDonation( new CancelDonationRequest(
			$donation->getId(),
			'token',
			'updateToken'
		) );

		$this->assertTrue( $response->cancellationWasSuccessful() );
	}

	public function testGivenIdOfNonCancellableDonation_cancellationIsNotSuccessful() {
		$factory = $this->newFactoryWithNullMailer();

		$donation = ValidDonation::newDonation();
		$donation->setStatus( Donation::STATUS_DELETED );
		$donation->setPaymentType( PaymentType::DIRECT_DEBIT );

		$factory->getDonationRepository()->storeDonation( $donation );

		$useCase = $factory->newCancelDonationUseCase();
		$response = $useCase->cancelDonation( new CancelDonationRequest(
			$donation->getId(),
			'token',
			'updateToken'
		) );

		$this->assertFalse( $response->cancellationWasSuccessful() );
	}

	public function testGivenValidRequest_cancellationConfirmationEmailIsSend() {
		$donation = $this->newCancelableDonation();

		$useCase = $this->newUseCaseWithMailerMock( $donation );

		$response = $useCase->cancelDonation( new CancelDonationRequest(
			$donation->getId(),
			'token',
			'updateToken'
		) );

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
				$this->equalTo( new MailAddress( $donation->getPersonalInfo()->getEmailAddress() ) ),
				$this->callback( function( $value ) {
					$this->assertInternalType( 'array', $value );
					// TODO: assert parameters
					return true;
				} )
			);

		return new CancelDonationUseCase(
			$this->getDonationRepositoryWithDonation( $donation ),
			$mailer
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
