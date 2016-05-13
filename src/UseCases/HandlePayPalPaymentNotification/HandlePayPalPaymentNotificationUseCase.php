<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\HandlePayPalPaymentNotification;

use Psr\Log\LoggerInterface;
use WMDE\Fundraising\Frontend\Domain\Model\PayPalData;
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
		// Avoid handling notifications that are not successes
		if ( !$request->isSuccessfulPaymentNotification() ) {
			$logContext = [
				'payment_status' => $request->getPaymentStatus(),
				'txn_id' => $request->getTransactionId()
			];
			$this->logger->info( 'Unhandled PayPal notification: ' . $request->getPaymentStatus(), $logContext );
			return false;
		}

		// Avoid handling successful notifications that are subscription-related but not payments
		if ( $this->transactionIsSubscriptionRelatedButNotAPayment( $request ) ) {
			$logContext = [
				'transaction_type' => $request->getTransactionType(),
				'txn_id' => $request->getTransactionId()
			];
			$this->logger->info( 'Unhandled PayPal subscription notification: ' . $request->getTransactionType(), $logContext );
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

		if ( !$this->authorizationService->canModifyDonation( $request->getDonationId() ) ) {
			return false;
		}

		try {
			$donation->addPayPalData( $this->newPayPalDataFromRequest( $request ) );
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

		try {
			$this->mailer->sendConfirmationMailFor( $donation );
		} catch ( \RuntimeException $ex ) {
			// TODO log mail error like we do in the add donation use case, see https://phabricator.wikimedia.org/T133549

			// no need to re-throw or return false, this is not a fatal error, only a minor inconvenience
		}

		return true;
	}

	private function transactionIsSubscriptionRelatedButNotAPayment( PayPalNotificationRequest $request ) {
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

}
