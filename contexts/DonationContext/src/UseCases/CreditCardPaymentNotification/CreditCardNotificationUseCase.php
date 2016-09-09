<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\UseCases\CreditCardPaymentNotification;

use Psr\Log\LoggerInterface;
use WMDE\Fundraising\Frontend\DonationContext\Authorization\DonationAuthorizer;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\GetDonationException;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\StoreDonationException;
use WMDE\Fundraising\Frontend\DonationContext\Infrastructure\DonationConfirmationMailer;
use WMDE\Fundraising\Frontend\DonationContext\Infrastructure\DonationEventLogger;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\CreditCardTransactionData;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\PaymentContext\Infrastructure\CreditCardService;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class CreditCardNotificationUseCase {

	private $repository;
	private $authorizationService;
	private $creditCardService;
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

	/**
	 * @param CreditCardPaymentNotificationRequest $request
	 * @throws CreditCardPaymentHandlerException
	 */
	public function handleNotification( CreditCardPaymentNotificationRequest $request ) {
		try {
			$donation = $this->repository->getDonationById( $request->getDonationId() );
		} catch ( GetDonationException $ex ) {
			throw new CreditCardPaymentHandlerException( 'data set could not be retrieved from database', $ex );
		}

		if ( $donation === null ) {
			throw new CreditCardPaymentHandlerException( 'donation not found' );
		}

		if ( $donation->getPaymentType() !== PaymentType::CREDIT_CARD ) {
			throw new CreditCardPaymentHandlerException( 'payment type mismatch' );
		}

		if ( !$donation->getAmount()->equals( $request->getAmount() ) ) {
			throw new CreditCardPaymentHandlerException( 'amount mismatch' );
		}

		if ( !$this->authorizationService->systemCanModifyDonation( $request->getDonationId() ) ) {
			throw new CreditCardPaymentHandlerException( 'invalid or expired token' );
		}

		$this->handleRequest( $request, $donation );
	}

	/**
	 * @param CreditCardPaymentNotificationRequest $request
	 * @param \WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation $donation
	 */
	private function handleRequest( CreditCardPaymentNotificationRequest $request, Donation $donation ) {
		try {
			$donation->addCreditCardData( $this->newCreditCardDataFromRequest( $request ) );
			$donation->confirmBooked();
		} catch ( \RuntimeException $e ) {
			throw new CreditCardPaymentHandlerException( 'data set could not be updated', $e );
		}

		try {
			$this->repository->storeDonation( $donation );
		}
		catch ( StoreDonationException $ex ) {
			throw new CreditCardPaymentHandlerException( 'updated data set could not be stored', $ex );
		}

		$this->sendConfirmationEmail( $donation );
		$this->donationEventLogger->log( $donation->getId(), 'mcp_handler: booked' );
	}

	private function sendConfirmationEmail( Donation $donation ) {
		if ( $donation->getDonor() !== null ) {
			try {
				$this->mailer->sendConfirmationMailFor( $donation );
			} catch ( \RuntimeException $ex ) {
				// no need to re-throw or return false, this is not a fatal error, only a minor inconvenience
			}
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
