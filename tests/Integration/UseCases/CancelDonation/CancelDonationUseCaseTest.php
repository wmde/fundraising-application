<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\CancelDonation;

use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Model\EmailAddress;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\Fixtures\DonationEventLoggerSpy;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeDonationRepository;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;
use WMDE\Fundraising\Frontend\Tests\Fixtures\SucceedingDonationAuthorizer;
use WMDE\Fundraising\Frontend\Tests\Fixtures\TemplateBasedMailerSpy;
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
		$factory->setTokenGenerator( new FixedTokenGenerator( self::CORRECT_UPDATE_TOKEN ) );

		return $factory;
	}

	public function testResponseContainsDonationId() {
		$useCase = $this->newUseCase();

		$response = $useCase->cancelDonation( new CancelDonationRequest( 1337 ) );

		$this->assertEquals( 1337, $response->getDonationId() );
	}

	public function testGivenIdOfCancellableDonation_cancellationIsSuccessful() {
		$donation = $this->newCancelableDonation();

		$useCase = new CancelDonationUseCase(
			new FakeDonationRepository( $donation ),
			new TemplateBasedMailerSpy( $this ),
			new SucceedingDonationAuthorizer(),
			new DonationEventLoggerSpy()
		);

		$response = $useCase->cancelDonation( new CancelDonationRequest( $donation->getId() ) );

		$this->assertTrue( $response->cancellationWasSuccessful() );
	}

	private function storeDonation( Donation $donation, FunFunFactory $factory ) {
		$factory->getDonationRepository()->storeDonation( $donation );
	}

	public function testGivenIdOfNonCancellableDonation_cancellationIsNotSuccessful() {
		$factory = $this->newFactoryWithNullMailer();

		$donation = ValidDonation::newDirectDebitDonation();
		$donation->cancel();

		$this->storeDonation( $donation, $factory );

		$useCase = $factory->newCancelDonationUseCase( self::CORRECT_UPDATE_TOKEN );
		$response = $useCase->cancelDonation( new CancelDonationRequest( $donation->getId() ) );

		$this->assertFalse( $response->cancellationWasSuccessful() );
	}

	private function newCancelableDonation(): Donation {
		return ValidDonation::newDirectDebitDonation();
	}

	public function testWhenDonationGetsCancelled_cancellationConfirmationEmailIsSend() {
		$donation = $this->newCancelableDonation();
		$mailerSpy = new TemplateBasedMailerSpy( $this );

		$this->saveAndCancelUsingMailer( $donation, $mailerSpy );

		$mailerSpy->assertCalledOnceWith(
			new EmailAddress( $donation->getDonor()->getEmailAddress() ),
			[
				'recipient' => [
					'lastName' => ValidDonation::DONOR_LAST_NAME,
					'salutation' => ValidDonation::DONOR_SALUTATION,
					'title' => ValidDonation::DONOR_TITLE
				],
				'donationId' => 1
			]
		);
	}

	private function saveAndCancelUsingMailer( Donation $donation, TemplateBasedMailer $mailer ) {
		$useCase = new CancelDonationUseCase(
			$this->getDonationRepositoryWithDonation( $donation ),
			$mailer,
			new SucceedingDonationAuthorizer(),
			new DonationEventLoggerSpy()
		);

		$response = $useCase->cancelDonation( new CancelDonationRequest( $donation->getId() ) );
		$this->assertTrue( $response->cancellationWasSuccessful() );
	}

	private function getDonationRepositoryWithDonation( Donation $donation ): DonationRepository {
		$donationRepository = TestEnvironment::newInstance()->getFactory()->getDonationRepository();

		$donationRepository->storeDonation( $donation );

		return $donationRepository;
	}

	public function testWhenDonationGetsCancelled_logEntryNeededByBackendIsWritten() {
		$donationLogger = new DonationEventLoggerSpy();
		$donation = $this->newCancelableDonation();

		$useCase = new CancelDonationUseCase(
			$this->getDonationRepositoryWithDonation( $donation ),
			new TemplateBasedMailerSpy( $this ),
			new SucceedingDonationAuthorizer(),
			$donationLogger
		);

		$useCase->cancelDonation( new CancelDonationRequest( $donation->getId() ) );

		$this->assertSame(
			[ [ $donation->getId(), 'frontend: storno' ] ],
			$donationLogger->getLogCalls()
		);
	}

	public function testGivenIdOfNonCancellableDonation_nothingIsWrittenToTheLog() {
		$donationLogger = new DonationEventLoggerSpy();

		$useCase = new CancelDonationUseCase(
			new FakeDonationRepository(),
			new TemplateBasedMailerSpy( $this ),
			new SucceedingDonationAuthorizer(),
			$donationLogger
		);

		$useCase->cancelDonation( new CancelDonationRequest( 1 ) );

		$this->assertSame( [], $donationLogger->getLogCalls() );
	}

}
