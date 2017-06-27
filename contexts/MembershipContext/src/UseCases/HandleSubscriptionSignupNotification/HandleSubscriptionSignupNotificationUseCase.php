<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\UseCases\HandleSubscriptionSignupNotification;

use Psr\Log\LoggerInterface;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateMailerInterface;
use WMDE\Fundraising\Frontend\MembershipContext\Authorization\ApplicationAuthorizer;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\Application;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Repositories\ApplicationRepository;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Repositories\GetMembershipApplicationException;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Repositories\StoreMembershipApplicationException;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PayPalData;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PayPalPayment;
use WMDE\Fundraising\Frontend\PaymentContext\ResponseModel\PaypalNotificationResponse;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class HandleSubscriptionSignupNotificationUseCase {

	private $repository;
	private $authorizationService;
	private $mailer;
	private $logger;

	public function __construct( ApplicationRepository $repository, ApplicationAuthorizer $authorizationService,
		TemplateMailerInterface $mailer, LoggerInterface $logger ) {

		$this->repository = $repository;
		$this->authorizationService = $authorizationService;
		$this->mailer = $mailer;
		$this->logger = $logger;
	}

	public function handleNotification( SubscriptionSignupRequest $request ): PaypalNotificationResponse {
		try {
			$membershipApplication = $this->repository->getApplicationById( $request->getApplicationId() );
		} catch ( GetMembershipApplicationException $ex ) {
			return $this->createErrorResponse( $ex );
		}

		if ( $membershipApplication === null ) {
			return $this->createUnhandledResponse( 'specified data set could not be found' );
		}

		return $this->handleRequestForMembershipApplication( $request, $membershipApplication );
	}

	private function handleRequestForMembershipApplication( SubscriptionSignupRequest $request,
															Application $application ): PaypalNotificationResponse {
		$paymentMethod = $application->getPayment()->getPaymentMethod();

		if ( !( $paymentMethod instanceof PayPalPayment ) ) {
			return $this->createUnhandledResponse( 'Trying to handle IPN for non-PayPal membership application' );
		}

		if ( !$this->authorizationService->canModifyApplication( $request->getApplicationId() ) ) {
			return $this->createUnhandledResponse( 'Wrong access code for membership application' );
		}

		$paymentMethod->addPayPalData( $this->newPayPalDataFromRequest( $request ) );

		try {
			$application->confirmSubscriptionCreated();
		} catch ( \RuntimeException $ex ) {
			return $this->createErrorResponse( $ex );
		}

		try {
			$this->repository->storeApplication( $application );
		}
		catch ( StoreMembershipApplicationException $ex ) {
			return $this->createErrorResponse( $ex );
		}

		$this->sendConfirmationEmail( $application );

		return PaypalNotificationResponse::newSuccessResponse();
	}

	private function createUnhandledResponse( string $reason ): PaypalNotificationResponse {
		return PaypalNotificationResponse::newUnhandledResponse( [
			'message' => $reason
		] );
	}

	private function createErrorResponse( \Exception $ex ): PaypalNotificationResponse {
		return PaypalNotificationResponse::newFailureResponse( [
			'message' => $ex->getMessage(),
			'stackTrace' => $ex->getTraceAsString()
		] );
	}

	private function sendConfirmationEmail( Application $application ) {
		try {
			$this->mailer->sendMail( $application->getApplicant()->getEmailAddress() );
		} catch ( \RuntimeException $ex ) {
			// no need to re-throw or return false, this is not a fatal error, only a minor inconvenience
		}
	}

	private function newPayPalDataFromRequest( SubscriptionSignupRequest $request ): PayPalData {
		return ( new PayPalData() )
			->setPayerId( $request->getPayerId() )
			->setSubscriberId( $request->getSubscriptionId() )
			->setPayerStatus( $request->getPayerStatus() )
			->setAddressStatus( $request->getPayerAddressStatus() )
			->setFirstName( $request->getPayerFirstName() )
			->setLastName( $request->getPayerLastName() )
			->setAddressName( $request->getPayerAddressName() )
			->setPaymentType( $request->getPaymentType() )
			->setPaymentStatus( implode( '/', [ $request->getPaymentType(), $request->getTransactionType() ] ) )
			->setCurrencyCode( $request->getCurrencyCode() );
	}

}
