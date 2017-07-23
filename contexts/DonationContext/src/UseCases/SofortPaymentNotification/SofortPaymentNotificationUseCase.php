<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\UseCases\SofortPaymentNotification;

use DateTime;
use RuntimeException;
use WMDE\Fundraising\Frontend\DonationContext\Authorization\DonationAuthorizer;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\GetDonationException;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\StoreDonationException;
use WMDE\Fundraising\Frontend\DonationContext\Infrastructure\DonationConfirmationMailer;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\SofortPayment;
use WMDE\Fundraising\Frontend\PaymentContext\ResponseModel\SofortNotificationResponse;
use WMDE\Fundraising\Frontend\PaymentContext\RequestModel\SofortNotificationRequest;

class SofortPaymentNotificationUseCase {

	private $repository;
	private $authorizationService;
	private $mailer;

	public function __construct( DonationRepository $repository, DonationAuthorizer $authorizationService,
								 DonationConfirmationMailer $mailer ) {
		$this->repository = $repository;
		$this->authorizationService = $authorizationService;
		$this->mailer = $mailer;
	}

	public function handleNotification( SofortNotificationRequest $request ): SofortNotificationResponse {
		try {
			$donation = $this->repository->getDonationById( $request->getDonationId() );
		} catch ( GetDonationException $ex ) {
			return $this->createFailureResponse( $ex );
		}

		if ( $donation === null ) {
			return $this->createFailureResponse( new RuntimeException( 'Donation not found' ) );
		}

		return $this->handleRequestForDonation( $request, $donation );
	}

	private function handleRequestForDonation( SofortNotificationRequest $request, Donation $donation ): SofortNotificationResponse {
		$paymentMethod = $donation->getPayment()->getPaymentMethod();

		if ( !( $paymentMethod instanceof SofortPayment ) ) {
			return $this->createUnhandledResponse( 'Trying to handle notification for non-sofort donation' );
		}

		if ( !$this->authorizationService->systemCanModifyDonation( $donation->getId() ) ) {
			return $this->createUnhandledResponse( 'Wrong access code for donation' );
		}

		if ( $paymentMethod->isConfirmedPayment() ) {
			return $this->createUnhandledResponse( 'Duplicate notification' );
		}

		$paymentMethod->setConfirmedAt( $request->getTime() );

		try {
			$this->repository->storeDonation( $donation );
		}
		catch ( StoreDonationException $ex ) {
			return $this->createFailureResponse( $ex );
		}

		$this->sendConfirmationEmailFor( $donation );

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
			} catch ( RuntimeException $ex ) {
				// no need to re-throw or return false, this is not a fatal error, only a minor inconvenience
			}
		}
	}

	private function createFailureResponse( RuntimeException $ex ): SofortNotificationResponse {
		return SofortNotificationResponse::newFailureResponse( [
			'message' => $ex->getMessage(),
			'stackTrace' => $ex->getTraceAsString()
		] );
	}

}
