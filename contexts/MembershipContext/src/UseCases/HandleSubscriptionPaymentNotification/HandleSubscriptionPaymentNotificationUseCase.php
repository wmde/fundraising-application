<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\UseCases\HandleSubscriptionPaymentNotification;

use Psr\Log\LoggerInterface;
use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateMailerInterface;
use WMDE\Fundraising\Frontend\MembershipContext\Authorization\ApplicationAuthorizer;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\Application;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\Payment;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Repositories\ApplicationRepository;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Repositories\GetMembershipApplicationException;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Repositories\StoreMembershipApplicationException;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PayPalData;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PayPalPayment;
use WMDE\Fundraising\Frontend\PaymentContext\RequestModel\PayPalPaymentNotificationRequest;
use WMDE\Fundraising\Frontend\PaymentContext\ResponseModel\PaypalNotificationResponse;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class HandleSubscriptionPaymentNotificationUseCase {

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

	public function handleNotification( PayPalPaymentNotificationRequest $request ): PaypalNotificationResponse {
		try {
			$membershipApplication = $this->repository->getApplicationById( $request->getInternalId() );
		} catch ( GetMembershipApplicationException $ex ) {
			return $this->createErrorResponse( $ex );
		}

		if ( $membershipApplication === null ) {
			return $this->createUnhandledResponse( 'specified data set could not be found' );
		}

		return $this->handleRequestForPayment( $request, $membershipApplication );
	}

	private function handleRequestForPayment( PayPalPaymentNotificationRequest $request,
											  Application $application ): PaypalNotificationResponse {
		if ( !( $application->getPayment()->getPaymentMethod() instanceof PayPalPayment ) ) {
			return $this->createUnhandledResponse( 'Trying to handle IPN for non-PayPal membership application' );
		}

		if ( !$this->authorizationService->canModifyApplication( $request->getInternalId() ) ) {
			return $this->createUnhandledResponse( 'Wrong access code for membership application' );
		}

		if ( $this->transactionAlreadyProcessed( $application, $request ) ) {
			return $this->createUnhandledResponse( 'Trying to handle IPN for already processed transaction' );
		}

		$childApplication = $this->createChildApplication( $application, $request );

		/** @var \WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PayPalPayment $payment */
		$payment = $application->getPayment()->getPaymentMethod();
		$payment->getPayPalData()->addChildPayment( $request->getTransactionId(), $childApplication->getId() );

		try {
			$this->repository->storeApplication( $application );
		}
		catch ( StoreMembershipApplicationException $ex ) {
			$this->createErrorResponse( $ex );
		}

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

	private function createChildApplication( Application $application, PayPalPaymentNotificationRequest $request ): ?Application {
		$childApplication = Application::newApplication(
			$application->getType(),
			$application->getApplicant(),
			new Payment(
				$application->getPayment()->getIntervalInMonths(),
				$application->getPayment()->getAmount(),
				new PayPalPayment( $this->newPayPalDataFromRequest( $request ) )
			)
		);
		$childApplication->confirm();

		try {
			$this->repository->storeApplication( $childApplication );
		}
		catch ( StoreMembershipApplicationException $ex ) {
			return null;
		}

		return $childApplication;
	}

	private function transactionAlreadyProcessed( Application $application, PayPalPaymentNotificationRequest $request ): bool {
		/** @var PayPalPayment $payment */
		$payment = $application->getPayment()->getPaymentMethod();
		return $payment->getPayPalData()->hasChildPayment( $request->getTransactionId() );
	}

}
