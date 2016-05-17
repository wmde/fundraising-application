<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\HandlePayPalPaymentNotification;

use Psr\Log\LoggerInterface;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Model\DonationPayment;
use WMDE\Fundraising\Frontend\Domain\Model\PayPalData;
use WMDE\Fundraising\Frontend\Domain\Model\PayPalPayment;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\Domain\Repositories\GetDonationException;
use WMDE\Fundraising\Frontend\Domain\Repositories\StoreDonationException;
use WMDE\Fundraising\Frontend\Infrastructure\DonationAuthorizer;
use WMDE\Fundraising\Frontend\Infrastructure\DonationConfirmationMailer;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class HandlePayPalPaymentNotificationUseCase {

	private $repository;
	private $authorizationService;
	private $mailer;
	private $logger;

	public function __construct( DonationRepository $repository, DonationAuthorizer $authorizationService,
								 DonationConfirmationMailer $mailer, LoggerInterface $logger ) {
		$this->repository = $repository;
		$this->authorizationService = $authorizationService;
		$this->mailer = $mailer;
		$this->logger = $logger;
	}

	public function handleNotification( PayPalNotificationRequest $request ): bool {
		if ( !$request->isSuccessfulPaymentNotification() ) {
			$this->logUnhandledStatus( $request );
			return false;
		}

		if ( $this->transactionIsSubscriptionRelatedButNotAPayment( $request ) ) {
			$this->logUnhandledNonPayment( $request );
			return false;
		}

		try {
			$donation = $this->repository->getDonationById( $request->getDonationId() );
		} catch ( GetDonationException $ex ) {
			return false;
		}

		if ( $donation === null ) {
			// TODO: create new donation

			// TODO: the id in PayPalNotificationRequest needs to be made nullable?

			// TODO: when id is null, dont try getting donation.
			// If it is not null, not finding donation should probably be error
			return true;
		}

		if ( !( $donation->getPayment()->getPaymentMethod() instanceof PayPalPayment ) ) {
			return false;
		}

		if ( !$this->authorizationService->canModifyDonation( $request->getDonationId() ) ) {
			return false;
		}

		if ( $this->donationWasBookedWithDifferentTransactionId( $donation, $request ) ) {
			$this->createChildDonation( $donation, $request );
			return true;
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
		try {
			$this->mailer->sendConfirmationMailFor( $donation );
		} catch ( \RuntimeException $ex ) {
			// TODO log mail error like we do in the add donation use case, see https://phabricator.wikimedia.org/T133549
			// no need to re-throw or return false, this is not a fatal error, only a minor inconvenience
		}
	}

	private function transactionIsSubscriptionRelatedButNotAPayment( PayPalNotificationRequest $request ): bool {
		$transactionType = $request->getTransactionType();
		return strpos( $transactionType, 'subscr_' ) === 0 && $transactionType !== 'subscr_payment';
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
			return;
		}
		/** @var PayPalPayment $paymentMethod */
		$paymentMethod = $payment->getPaymentMethod();
		$paymentMethod->getPayPalData()->addChildPayment( $request->getTransactionId(), $childDonation->getId() );
		try {
			$this->repository->storeDonation( $donation );
		} catch ( StoreDonationException $ex ) {
			return;
		}

	}
}
