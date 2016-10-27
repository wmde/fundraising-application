<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\UseCases\HandlePayPalPaymentNotification;

use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\DonationContext\Authorization\DonationAuthorizer;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonationPayment;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonationTrackingInfo;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donor;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonorAddress;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonorName;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\GetDonationException;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\StoreDonationException;
use WMDE\Fundraising\Frontend\DonationContext\Infrastructure\DonationConfirmationMailer;
use WMDE\Fundraising\Frontend\DonationContext\Infrastructure\DonationEventLogger;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PayPalData;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PayPalPayment;
use WMDE\Fundraising\Frontend\PaymentContext\ResponseModel\PaypalNotificationResponse;
use WMDE\Fundraising\Frontend\PaymentContext\RequestModel\PayPalPaymentNotificationRequest;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class HandlePayPalPaymentNotificationUseCase {

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

	public function handleNotification( PayPalPaymentNotificationRequest $request ): PaypalNotificationResponse {
		if ( !$request->isSuccessfulPaymentNotification() ) {
			return $this->createUnhandledResponse( 'Unhandled PayPal instant payment notification' );
		}

		if ( $this->transactionIsSubscriptionRelatedButNotAPayment( $request ) ) {
			return $this->createUnhandledResponse( 'Unhandled PayPal subscription notification' );
		}

		try {
			$donation = $this->repository->getDonationById( $request->getInternalId() );
		} catch ( GetDonationException $ex ) {
			return $this->createErrorResponse( $ex );
		}

		if ( $donation === null ) {
			return $this->handleRequestWithoutDonation( $request );
		}

		return $this->handleRequestForDonation( $request, $donation );
	}

	private function handleRequestWithoutDonation( PayPalPaymentNotificationRequest $request ): PaypalNotificationResponse {
		$donation = $this->newDonationFromRequest( $request );

		try {
			$this->repository->storeDonation( $donation );
		} catch ( StoreDonationException $ex ) {
			return $this->createErrorResponse( $ex );
		}

		$this->sendConfirmationEmailFor( $donation );
		$this->donationEventLogger->log( $donation->getId(), 'paypal_handler: booked' );

		return PaypalNotificationResponse::newSuccessResponse();
	}

	private function handleRequestForDonation( PayPalPaymentNotificationRequest $request, Donation $donation ): PaypalNotificationResponse {
		if ( !( $donation->getPayment()->getPaymentMethod() instanceof PayPalPayment ) ) {
			return $this->createUnhandledResponse( 'Trying to handle IPN for non-Paypal donation' );
		}

		if ( !$this->authorizationService->systemCanModifyDonation( $request->getInternalId() ) ) {
			return $this->createUnhandledResponse( 'Wrong access code for donation' );
		}
		if ( $this->donationWasBookedWithDifferentTransactionId( $donation, $request ) ) {
			return $this->createChildDonation( $donation, $request );
		}

		$donation->addPayPalData( $this->newPayPalDataFromRequest( $request ) );

		try {
			$donation->confirmBooked();
		} catch ( \RuntimeException $ex ) {
			return $this->createErrorResponse( $ex );
		}

		try {
			$this->repository->storeDonation( $donation );
		}
		catch ( StoreDonationException $ex ) {
			return $this->createErrorResponse( $ex );
		}

		$this->sendConfirmationEmailFor( $donation );
		$this->donationEventLogger->log( $donation->getId(), 'paypal_handler: booked' );

		return PaypalNotificationResponse::newSuccessResponse();
	}

	private function createUnhandledResponse( string $reason ): PaypalNotificationResponse {
		return PaypalNotificationResponse::newUnhandledResponse( [
			'message' => $reason
		] );
	}

	private function sendConfirmationEmailFor( Donation $donation ) {
		if ( $donation->getDonor() !== null ) {
			try {
				$this->mailer->sendConfirmationMailFor( $donation );
			} catch ( \RuntimeException $ex ) {
				// no need to re-throw or return false, this is not a fatal error, only a minor inconvenience
			}
		}
	}

	private function transactionIsSubscriptionRelatedButNotAPayment( PayPalPaymentNotificationRequest $request ): bool {
		return $request->isForRecurringPayment() && !$request->isRecurringPaymentCompletion();
	}

	private function newPayPalDataFromRequest( PayPalPaymentNotificationRequest $request ): PayPalData {
		return ( new PayPalData() )
			->setPayerId( $request->getPayerId() )
			->setSubscriberId( $request->getSubscriptionId() )
			->setPayerStatus( $request->getPayerStatus() )
			->setAddressStatus( $request->getPayerAddressStatus() )
			->setAmount( $request->getAmountGross() )
			->setCurrencyCode( $request->getCurrencyCode() )
			->setFee( Euro::newFromString( $request->getTransactionFee() ) )
			->setSettleAmount( $request->getSettleAmount() )
			->setFirstName( $request->getPayerFirstName() )
			->setLastName( $request->getPayerLastName() )
			->setAddressName( $request->getPayerAddressName() )
			->setPaymentId( $request->getTransactionId() )
			->setPaymentType( $request->getPaymentType() )
			->setPaymentStatus( implode( '/', [ $request->getPaymentStatus(), $request->getTransactionType() ] ) )
			->setPaymentTimestamp( $request->getPaymentTimestamp() );
	}

	private function donationWasBookedWithDifferentTransactionId( Donation $donation,
																  PayPalPaymentNotificationRequest $request ): bool {
		/**
		 * @var PayPalPayment $payment
		 */
		$payment = $donation->getPaymentMethod();

		if ( !$donation->isBooked() ) {
			return false;
		}

		if ( $request->getTransactionId() === $payment->getPayPalData()->getPaymentId() ) {
			return false;
		}

		if ( $payment->getPayPalData()->hasChildPayment( $request->getTransactionId() ) ) {
			return false;
		}

		return true;
	}

	private function createChildDonation( Donation $donation, PayPalPaymentNotificationRequest $request ): PaypalNotificationResponse {
		$childPaymentMethod = new PayPalPayment( $this->newPayPalDataFromRequest( $request ) );
		$payment = $donation->getPayment();
		$childDonation = new Donation(
			null,
			Donation::STATUS_EXTERNAL_BOOKED,
			$donation->getDonor(),
			new DonationPayment( $payment->getAmount(), $payment->getIntervalInMonths(), $childPaymentMethod ),
			$donation->getOptsIntoNewsletter(), $donation->getTrackingInfo()
		);
		try {
			$this->repository->storeDonation( $childDonation );
		} catch ( StoreDonationException $ex ) {
			return $this->createErrorResponse( $ex );
		}
		/** @var \WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PayPalPayment $paymentMethod */
		$paymentMethod = $payment->getPaymentMethod();
		$paymentMethod->getPayPalData()->addChildPayment( $request->getTransactionId(), $childDonation->getId() );
		try {
			$this->repository->storeDonation( $donation );
		} catch ( StoreDonationException $ex ) {
			return $this->createErrorResponse( $ex );
		}
		$this->logChildDonationCreatedEvent( $donation->getId(), $childDonation->getId() );
		return PaypalNotificationResponse::newSuccessResponse();
	}

	private function logChildDonationCreatedEvent( $parentId, $childId ) {
		$this->donationEventLogger->log(
			$parentId,
			"paypal_handler: new transaction id to corresponding child donation: $childId"
		);
		$this->donationEventLogger->log(
			$childId,
			"paypal_handler: new transaction id to corresponding parent donation: $parentId"
		);
	}

	private function newDonorFromRequest( PayPalPaymentNotificationRequest $request ): Donor {
		return new Donor(
			$this->newPersonNameFromRequest( $request ),
			$this->newPhysicalAddressFromRequest( $request ),
			$request->getPayerEmail()
		);
	}

	private function newPersonNameFromRequest( PayPalPaymentNotificationRequest $request ): DonorName {
		$name = DonorName::newPrivatePersonName();
		$name->setFirstName( $request->getPayerFirstName() );
		$name->setLastName( $request->getPayerLastName() );
		$name->freeze();
		return $name;
	}

	private function newPhysicalAddressFromRequest( PayPalPaymentNotificationRequest $request ): DonorAddress {
		$address = new DonorAddress();
		$address->setStreetAddress( $request->getPayerAddressStreet() );
		$address->setCity( $request->getPayerAddressCity() );
		$address->setPostalCode( $request->getPayerAddressPostalCode() );
		$address->setCountryCode( $request->getPayerAddressCountryCode() );
		$address->freeze();
		return $address;
	}

	private function newDonationFromRequest( PayPalPaymentNotificationRequest $request ): Donation {
		$payment = new DonationPayment( $request->getAmountGross(), 0, new PayPalPayment() );
		$donation = new Donation(
			null,
			Donation::STATUS_EXTERNAL_BOOKED,
			$this->newDonorFromRequest( $request ),
			$payment,
			Donation::DOES_NOT_OPT_INTO_NEWSLETTER,
			DonationTrackingInfo::newBlankTrackingInfo()->freeze()->assertNoNullFields()
		);
		$donation->addPayPalData( $this->newPayPalDataFromRequest( $request ) );
		return $donation;
	}

	private function createErrorResponse( \Exception $ex ): PaypalNotificationResponse {
		return PaypalNotificationResponse::newFailureResponse( [
			'message' => $ex->getMessage(),
			'stackTrace' => $ex->getTraceAsString()
		] );
	}

}
