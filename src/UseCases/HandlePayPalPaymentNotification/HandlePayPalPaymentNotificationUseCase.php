<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\HandlePayPalPaymentNotification;

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

	public function __construct( DonationRepository $repository, DonationAuthorizer $authorizationService,
								 DonationConfirmationMailer $mailer ) {
		$this->repository = $repository;
		$this->authorizationService = $authorizationService;
		$this->mailer = $mailer;
	}

	public function handleNotification( PayPalNotificationRequest $request ): bool {
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
			$this->mailer->sendMailFromDonation( $donation );
		} catch ( \RuntimeException $ex ) {
			// TODO log mail error like we do in the add donation use case, see https://phabricator.wikimedia.org/T133549

			// no need to re-throw or return false, this is not a fatal error, only a minor inconvenience
		}

		return true;
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
