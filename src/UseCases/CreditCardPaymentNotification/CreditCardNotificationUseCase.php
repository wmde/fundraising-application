<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\CreditCardPaymentNotification;

use Psr\Log\LoggerInterface;
use WMDE\Fundraising\Frontend\Domain\Model\CreditCardTransactionData;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\Domain\Repositories\GetDonationException;
use WMDE\Fundraising\Frontend\Domain\Repositories\StoreDonationException;
use WMDE\Fundraising\Frontend\Infrastructure\CreditCardExpiryFetchingException;
use WMDE\Fundraising\Frontend\Infrastructure\CreditCardService;
use WMDE\Fundraising\Frontend\Infrastructure\DonationAuthorizer;
use WMDE\Fundraising\Frontend\Infrastructure\DonationConfirmationMailer;
use WMDE\Fundraising\Frontend\Infrastructure\DonationEventLogger;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class CreditCardNotificationUseCase {

	private $repository;
	private $authorizationService;
	private $mailer;
	private $logger;
	private $donationEventLogger;

	public function __construct( DonationRepository $repository, DonationAuthorizer $authorizationService,
								 CreditCardService $creditCardService, DonationConfirmationMailer $mailer,
								 LoggerInterface $logger, DonationEventLogger $donationEventLogger ) {
		$this->repository = $repository;
		$this->authorizationService = $authorizationService;
		$this->creditCardService = $creditCardService;
		$this->mailer = $mailer;
		$this->logger = $logger;
		$this->donationEventLogger = $donationEventLogger;
	}

	public function handleNotification( CreditCardPaymentNotificationRequest $request ): bool {
		try {
			$donation = $this->repository->getDonationById( $request->getDonationId() );
		} catch ( GetDonationException $ex ) {
			return false;
		}

		if ( $donation === null ) {
			return false;
		}

		if ( $donation->getPaymentType() !== PaymentType::CREDIT_CARD ) {
			return false;
		}

		if ( !$donation->getAmount()->equals( $request->getAmount() ) ) {
			return false;
		}

		if ( !$this->authorizationService->systemCanModifyDonation( $request->getDonationId() ) ) {
			return false;
		}

		return $this->handleRequest( $request, $donation );
	}

	private function handleRequest( CreditCardPaymentNotificationRequest $request, Donation $donation ): bool {
		try {
			$donation->addCreditCardData( $this->newCreditCardDataFromRequest( $request ) );
			$donation->confirmBooked();
		} catch ( \RuntimeException $e ) {
			return false;
		}

		try {
			$this->repository->storeDonation( $donation );
		}
		catch ( StoreDonationException $ex ) {
			return false;
		}

		$this->sendConfirmationEmail( $donation );
		return true;
	}

	private function sendConfirmationEmail( Donation $donation ) {
		try {
			$this->mailer->sendConfirmationMailFor( $donation );
		} catch ( \RuntimeException $ex ) {
			// no need to re-throw or return false, this is not a fatal error, only a minor inconvenience
		}
	}

	private function newCreditCardDataFromRequest( CreditCardPaymentNotificationRequest $request ): CreditCardTransactionData {
		return ( new CreditCardTransactionData() )
			->setTransactionId( $request->getTransactionId() )
			->setTransactionStatus( 'processed' )
			->setTransactionTimestamp( new \DateTime() )
			->setCardExpiry( $this->creditCardService->getExpirationDate( $request->getCustomerId() ) )
			->setAmount( $request->getAmount() )
			->setCustomerId( $request->getCustomerId() )
			->setSessionId( $request->getSessionId() )
			->setAuthId( $request->getAuthId() )
			->setTitle( $request->getTitle() )
			->setCountryCode( $request->getCountry() )
			->setCurrencyCode( $request->getCurrency() );
	}

}
