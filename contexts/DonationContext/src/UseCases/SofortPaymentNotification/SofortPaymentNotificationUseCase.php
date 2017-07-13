<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\UseCases\SofortPaymentNotification;

use WMDE\Fundraising\Frontend\DonationContext\Authorization\DonationAuthorizer;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\GetDonationException;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\StoreDonationException;
use WMDE\Fundraising\Frontend\DonationContext\Infrastructure\DonationConfirmationMailer;
use WMDE\Fundraising\Frontend\DonationContext\Infrastructure\DonationEventLogger;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\SofortPayment;
use WMDE\Fundraising\Frontend\PaymentContext\ResponseModel\SofortNotificationResponse;
use WMDE\Fundraising\Frontend\PaymentContext\RequestModel\SofortNotificationRequest;

class SofortPaymentNotificationUseCase {

	private $repository;
	private $authorizationService;
	private $mailer;
	private $donationEventLogger;

	public function __construct( DonationRepository $repository, DonationAuthorizer $authorizationService,
								 DonationConfirmationMailer $mailer, DonationEventLogger $donationEventLogger ) {
		$this->repository = $repository;
		$this->authorizationService = $authorizationService;
		$this->mailer = $mailer;
		$this->donationEventLogger = $donationEventLogger;
	}

	public function handleNotification( SofortNotificationRequest $request ): SofortNotificationResponse {

		try {
			$donation = $this->repository->getDonationById( $request->getDonationId() );
		} catch ( GetDonationException $ex ) {
			return $this->createErrorResponse( $ex );
		}

		if ( $donation === null ) {
			return $this->createErrorResponse( new \Exception( 'Donation not found' ) );
		}

		return $this->handleRequestForDonation( $request, $donation );
	}

	private function handleRequestForDonation( SofortNotificationRequest $request, Donation $donation ): SofortNotificationResponse {
		$paymentMethod = $donation->getPayment()->getPaymentMethod();

		if ( !( $paymentMethod instanceof SofortPayment ) ) {
			return $this->createUnhandledResponse( 'Trying to handle notification for non-sofort donation' );
		}
		if ( !$this->authorizationService->systemCanModifyDonation( $request->getDonationId() ) ) {
			return $this->createUnhandledResponse( 'Wrong access code for donation' );
		}

		$paymentMethod->setConfirmedAt( $request->getTime() );

		// todo ?
		//$donation->confirmBooked();

		try {
			$this->repository->storeDonation( $donation );
		}
		catch ( StoreDonationException $ex ) {
			return $this->createErrorResponse( $ex );
		}

		$this->sendConfirmationEmailFor( $donation );
		$this->donationEventLogger->log( $donation->getId(), 'sofort_handler: booked' );

		return SofortNotificationResponse::newSuccessResponse();
	}

	private function createUnhandledResponse( string $reason ): SofortNotificationResponse {
		return SofortNotificationResponse::newUnhandledResponse( [
			'message' => $reason
		] );
	}

	private function sendConfirmationEmailFor( Donation $donation ): void {
		if ( $donation->getDonor() !== null ) {
			try {
				$this->mailer->sendConfirmationMailFor( $donation );
			} catch ( \RuntimeException $ex ) {
				// no need to re-throw or return false, this is not a fatal error, only a minor inconvenience
			}
		}
	}

	private function createErrorResponse( \Exception $ex ): SofortNotificationResponse {
		return SofortNotificationResponse::newFailureResponse( [
			'message' => $ex->getMessage(),
			'stackTrace' => $ex->getTraceAsString()
		] );
	}

}
