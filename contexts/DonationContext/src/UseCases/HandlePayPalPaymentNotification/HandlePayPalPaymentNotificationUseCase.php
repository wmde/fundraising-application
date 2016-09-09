<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\UseCases\HandlePayPalPaymentNotification;

use Psr\Log\LoggerInterface;
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

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class HandlePayPalPaymentNotificationUseCase {

	private $repository;
	private $authorizationService;
	private $mailer;
	private $logger;
	private $donationEventLogger;

	public function __construct( DonationRepository $repository, DonationAuthorizer $authorizationService,
								 DonationConfirmationMailer $mailer, LoggerInterface $logger,
								 DonationEventLogger $donationEventLogger ) {
		$this->repository = $repository;
		$this->authorizationService = $authorizationService;
		$this->mailer = $mailer;
		$this->logger = $logger;
		$this->donationEventLogger = $donationEventLogger;
	}

	public function handleNotification( PayPalNotificationRequest $request ): bool {
		if ( !$this->requestCanBeHandled( $request ) ) {
			return false;
		}

		try {
			$donation = $this->repository->getDonationById( $request->getDonationId() );
		} catch ( GetDonationException $ex ) {
			return false;
		}

		if ( $donation === null ) {
			return $this->handleRequestWithoutDonation( $request );
		}

		if ( $donation->isBooked() && $request->isRecurringPaymentCompletion() ) {
			return $this->handleRequestWithoutDonation( $request );
		}

		return $this->handleRequestForDonation( $request, $donation );
	}

	private function requestCanBeHandled( PayPalNotificationRequest $request ): bool {
		if ( !$request->isSuccessfulPaymentNotification() ) {
			$this->logUnhandledStatus( $request );
			return false;
		}

		if ( $this->transactionIsSubscriptionRelatedButNotAPayment( $request ) ) {
			$this->logUnhandledNonPayment( $request );
			return false;
		}
		return true;
	}

	private function handleRequestWithoutDonation( PayPalNotificationRequest $request ): bool {
		$donation = $this->newDonationFromRequest( $request );

		try {
			$this->repository->storeDonation( $donation );
		} catch ( StoreDonationException $ex ) {
			return false;
		}

		$this->sendConfirmationEmailFor( $donation );
		$this->donationEventLogger->log( $donation->getId(), 'paypal_handler: booked' );

		return true;
	}

	private function handleRequestForDonation( PayPalNotificationRequest $request, Donation $donation ): bool {
		if ( !( $donation->getPayment()->getPaymentMethod() instanceof PayPalPayment ) ) {
			return false;
		}

		if ( !$this->authorizationService->systemCanModifyDonation( $request->getDonationId() ) ) {
			return false;
		}
		if ( $this->donationWasBookedWithDifferentTransactionId( $donation, $request ) ) {
			$childDonation = $this->createChildDonation( $donation, $request );
			return $childDonation !== null;
		}

		$donation->addPayPalData( $this->newPayPalDataFromRequest( $request ) );

		try {
			$donation->confirmBooked();
		} catch ( \RuntimeException $ex ) {
			return false;
		}

		try {
			$this->repository->storeDonation( $donation );
		}
		catch ( StoreDonationException $ex ) {
			return false;
		}

		$this->sendConfirmationEmailFor( $donation );
		$this->donationEventLogger->log( $donation->getId(), 'paypal_handler: booked' );

		return true;
	}

	private function logUnhandledStatus( PayPalNotificationRequest $request ) {
		$logContext = [
			'payment_status' => $request->getPaymentStatus(),
			'txn_id' => $request->getTransactionId()
		];
		$this->logger->info( 'Unhandled PayPal notification: ' . $request->getPaymentStatus(), $logContext );
	}

	private function logUnhandledNonPayment( PayPalNotificationRequest $request ) {
		$logContext = [
			'transaction_type' => $request->getTransactionType(),
			'txn_id' => $request->getTransactionId()
		];
		$this->logger->info( 'Unhandled PayPal subscription notification: ' . $request->getTransactionType(), $logContext );
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

	private function transactionIsSubscriptionRelatedButNotAPayment( PayPalNotificationRequest $request ): bool {
		return $request->isForRecurringPayment() && !$request->isRecurringPaymentCompletion();
	}

	private function newPayPalDataFromRequest( PayPalNotificationRequest $request ): PayPalData {
		return ( new PayPalData() )
			->setPayerId( $request->getPayerId() )
			->setSubscriberId( $request->getSubscriberId() )
			->setPayerStatus( $request->getPayerStatus() )
			->setAddressStatus( $request->getPayerAddressStatus() )
			->setAmount( $request->getAmountGross() )
			->setCurrencyCode( $request->getCurrencyCode() )
			->setFee( $request->getTransactionFee() )
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
																  PayPalNotificationRequest $request ): bool {
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

	private function createChildDonation( Donation $donation, PayPalNotificationRequest $request ) {
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
			return null;
		}
		/** @var \WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PayPalPayment $paymentMethod */
		$paymentMethod = $payment->getPaymentMethod();
		$paymentMethod->getPayPalData()->addChildPayment( $request->getTransactionId(), $childDonation->getId() );
		try {
			$this->repository->storeDonation( $donation );
		} catch ( StoreDonationException $ex ) {
			return null;
		}
		$this->logChildDonationCreatedEvent( $donation->getId(), $childDonation->getId() );
		return $childDonation;
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

	private function newDonorFromRequest( PayPalNotificationRequest $request ): Donor {
		return new Donor(
			$this->newPersonNameFromRequest( $request ),
			$this->newPhysicalAddressFromRequest( $request ),
			$request->getPayerEmail()
		);
	}

	private function newPersonNameFromRequest( PayPalNotificationRequest $request ): DonorName {
		$name = DonorName::newPrivatePersonName();
		$name->setFirstName( $request->getPayerFirstName() );
		$name->setLastName( $request->getPayerLastName() );
		$name->freeze();
		return $name;
	}

	private function newPhysicalAddressFromRequest( PayPalNotificationRequest $request ): DonorAddress {
		$address = new DonorAddress();
		$address->setStreetAddress( $request->getPayerAddressStreet() );
		$address->setCity( $request->getPayerAddressCity() );
		$address->setPostalCode( $request->getPayerAddressPostalCode() );
		$address->setCountryCode( $request->getPayerAddressCountryCode() );
		$address->freeze();
		return $address;
	}

	private function newDonationFromRequest( PayPalNotificationRequest $request ): Donation {
		$payment = new DonationPayment( $request->getAmountGross(), 0, new PayPalPayment() );
		$donation = new Donation(
			null,
			Donation::STATUS_EXTERNAL_BOOKED,
			$this->newDonorFromRequest( $request ),
			$payment,
			Donation::DOES_NOT_OPT_INTO_NEWSLETTER,
			new DonationTrackingInfo()
		);
		$donation->addPayPalData( $this->newPayPalDataFromRequest( $request ) );
		return $donation;
	}
}
