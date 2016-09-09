<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Tests\Integration\UseCases\CancelDonation;

use WMDE\Fundraising\Frontend\DonationContext\Authorization\DonationAuthorizer;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\CancelDonation\CancelDonationRequest;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\CancelDonation\CancelDonationResponse;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\CancelDonation\CancelDonationUseCase;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\EmailAddress;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\DonationEventLoggerSpy;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\FakeDonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\SucceedingDonationAuthorizer;
use WMDE\Fundraising\Frontend\Tests\Fixtures\TemplateBasedMailerSpy;

/**
 * @covers WMDE\Fundraising\Frontend\DonationContext\UseCases\CancelDonation\CancelDonationUseCase
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CancelDonationUseCaseTest extends \PHPUnit_Framework_TestCase {

	const CORRECT_UPDATE_TOKEN = 'b5b249c8beefb986faf8d186a3f16e86ef509ab2';

	/**
	 * @var FakeDonationRepository
	 */
	private $repository;

	/**
	 * @var TemplateBasedMailer|TemplateBasedMailerSpy
	 */
	private $mailer;

	/**
	 * @var DonationAuthorizer
	 */
	private $authorizer;

	/**
	 * @var DonationEventLoggerSpy
	 */
	private $logger;

	public function setUp() {
		$this->repository = new FakeDonationRepository();
		$this->mailer = new TemplateBasedMailerSpy( $this );
		$this->authorizer = new SucceedingDonationAuthorizer();
		$this->logger = new DonationEventLoggerSpy();
	}

	private function newCancelDonationUseCase(): CancelDonationUseCase {
		return new CancelDonationUseCase(
			$this->repository,
			$this->mailer,
			$this->authorizer,
			$this->logger
		);
	}

	public function testGivenIdOfUnknownDonation_cancellationIsNotSuccessful() {
		$response = $this->newCancelDonationUseCase()->cancelDonation( new CancelDonationRequest( 1 ) );

		$this->assertFalse( $response->cancellationSucceeded() );
	}

	public function testResponseContainsDonationId() {
		$response = $this->newCancelDonationUseCase()->cancelDonation( new CancelDonationRequest( 1337 ) );

		$this->assertSame( 1337, $response->getDonationId() );
	}

	public function testGivenIdOfCancellableDonation_cancellationIsSuccessful() {
		$donation = $this->newCancelableDonation();
		$this->repository->storeDonation( $donation );

		$request = new CancelDonationRequest( $donation->getId() );
		$response = $this->newCancelDonationUseCase()->cancelDonation( $request );

		$this->assertTrue( $response->cancellationSucceeded() );
		$this->assertFalse( $response->mailDeliveryFailed() );

		$this->assertTrue( $this->repository->getDonationById( $donation->getId() )->isCancelled() );
	}

	public function testGivenIdOfNonCancellableDonation_cancellationIsNotSuccessful() {
		$donation = $this->newCancelableDonation();
		$donation->cancel();
		$this->repository->storeDonation( $donation );

		$request = new CancelDonationRequest( $donation->getId() );
		$response = $this->newCancelDonationUseCase()->cancelDonation( $request );

		$this->assertFalse( $response->cancellationSucceeded() );
	}

	private function newCancelableDonation(): Donation {
		return ValidDonation::newDirectDebitDonation();
	}

	public function testWhenDonationGetsCancelled_cancellationConfirmationEmailIsSend() {
		$donation = $this->newCancelableDonation();
		$this->repository->storeDonation( $donation );

		$request = new CancelDonationRequest( $donation->getId() );
		$response = $this->newCancelDonationUseCase()->cancelDonation( $request );

		$this->assertTrue( $response->cancellationSucceeded() );

		$this->mailer->assertCalledOnceWith(
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

	public function testWhenDonationGetsCancelled_logEntryNeededByBackendIsWritten() {
		$donation = $this->newCancelableDonation();
		$this->repository->storeDonation( $donation );

		$this->newCancelDonationUseCase()->cancelDonation( new CancelDonationRequest( $donation->getId() ) );

		$this->assertSame(
			[ [ $donation->getId(), 'frontend: storno' ] ],
			$this->logger->getLogCalls()
		);
	}

	public function testGivenIdOfNonCancellableDonation_nothingIsWrittenToTheLog() {
		$this->newCancelDonationUseCase()->cancelDonation( new CancelDonationRequest( 1 ) );

		$this->assertSame( [], $this->logger->getLogCalls() );
	}

	public function testWhenConfirmationMailFails_mailDeliveryFailureResponseIsReturned() {
		$this->mailer = $this->newThrowingMailer();

		$response = $this->getResponseForCancellableDonation();

		$this->assertTrue( $response->cancellationSucceeded() );
		$this->assertTrue( $response->mailDeliveryFailed() );
	}

	private function newThrowingMailer(): TemplateBasedMailer {
		$mailer = $this->createMock( TemplateBasedMailer::class );

		$mailer->method( $this->anything() )->willThrowException( new \RuntimeException() );

		return $mailer;
	}

	public function testWhenGetDonationFails_cancellationIsNotSuccessful() {
		$this->repository->throwOnRead();

		$response = $this->getResponseForCancellableDonation();

		$this->assertFalse( $response->cancellationSucceeded() );
	}

	private function getResponseForCancellableDonation(): CancelDonationResponse {
		$donation = $this->newCancelableDonation();
		$this->repository->storeDonation( $donation );

		$request = new CancelDonationRequest( $donation->getId() );
		return $this->newCancelDonationUseCase()->cancelDonation( $request );
	}

	public function testWhenDonationSavingFails_cancellationIsNotSuccessful() {
		$donation = $this->newCancelableDonation();
		$this->repository->storeDonation( $donation );

		$this->repository->throwOnWrite();

		$request = new CancelDonationRequest( $donation->getId() );
		$response = $this->newCancelDonationUseCase()->cancelDonation( $request );

		$this->assertFalse( $response->cancellationSucceeded() );
	}

}
