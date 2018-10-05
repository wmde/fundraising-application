<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\RouteHandlers;

use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Euro\Euro;
use WMDE\Fundraising\PaymentContext\ResponseModel\PaypalNotificationResponse;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\Payment\PayPalPaymentNotificationVerifierException;
use WMDE\Fundraising\PaymentContext\RequestModel\PayPalPaymentNotificationRequest;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PayPalNotificationHandler {

	private const MSG_NOT_HANDLED = 'PayPal request not handled';

	private $ffFactory;

	public function __construct( FunFunFactory $ffFactory ) {
		$this->ffFactory = $ffFactory;
	}

	public function handle( Request $request ): Response {
		$post = $request->request;

		try {
			$this->ffFactory->getPayPalPaymentNotificationVerifier()->verify( $post->all() );
		} catch ( PayPalPaymentNotificationVerifierException $e ) {
			$this->ffFactory->getPaypalLogger()->log( LogLevel::ERROR, $e->getMessage(), [
				'post_vars' => $request->request->all()
			] );
			return $this->createErrorResponse( $e );
		}

		if ( !$this->requestIsForPaymentCompletion( $post ) ) {
			$this->ffFactory->getPaypalLogger()->log( LogLevel::INFO, self::MSG_NOT_HANDLED, [ 'post_vars' => $post->all() ] );
			return new Response( '', Response::HTTP_OK );
		}

		$useCase = $this->ffFactory->newHandlePayPalPaymentCompletionNotificationUseCase( $this->getUpdateToken( $post ) );

		$response = $useCase->handleNotification( $this->newUseCaseRequestFromPost( $post ) );
		$this->logResponseIfNeeded( $response, $request );

		return new Response( '', Response::HTTP_OK ); # PayPal expects an empty response
	}

	private function getUpdateToken( ParameterBag $postRequest ): string {
		return $this->getValueFromCustomVars( $postRequest->get( 'custom', '' ), 'utoken' );
	}

	private function getValueFromCustomVars( string $customVars, string $key ): string {
		$vars = json_decode( $customVars, true );
		return !empty( $vars[$key] ) ? $vars[$key] : '';
	}

	private function requestIsForPaymentCompletion( ParameterBag $post ): bool {
		if ( !$this->isSuccessfulPaymentNotification( $post ) ) {
			return false;
		}
		if ( $this->isForRecurringPayment( $post ) && !$this->isRecurringPaymentCompletion( $post ) ) {
			return false;
		}

		return true;
	}

	private function isSuccessfulPaymentNotification( ParameterBag $post ): bool {
		return $post->get( 'payment_status', '' ) === 'Completed' || $post->get( 'payment_status' ) === 'Processed';
	}

	private function isRecurringPaymentCompletion( ParameterBag $post ): bool {
		return $post->get( 'txn_type', '' ) === 'subscr_payment';
	}

	private function isForRecurringPayment( ParameterBag $post ): bool {
		return strpos( $post->get( 'txn_type', '' ), 'subscr_' ) === 0;
	}

	private function newUseCaseRequestFromPost( ParameterBag $postRequest ): PayPalPaymentNotificationRequest {
		return ( new PayPalPaymentNotificationRequest() )
			->setTransactionType( $postRequest->get( 'txn_type', '' ) )
			->setTransactionId( $postRequest->get( 'txn_id', '' ) )
			->setPayerId( $postRequest->get( 'payer_id', '' ) )
			->setSubscriptionId( $postRequest->get( 'subscr_id', '' ) )
			->setPayerEmail( $postRequest->get( 'payer_email', '' ) )
			->setPayerStatus( $postRequest->get( 'payer_status', '' ) )
			->setPayerFirstName( $postRequest->get( 'first_name', '' ) )
			->setPayerLastName( $postRequest->get( 'last_name', '' ) )
			->setPayerAddressName( $postRequest->get( 'address_name', '' ) )
			->setPayerAddressStreet( $postRequest->get( 'address_street', '' ) )
			->setPayerAddressPostalCode( $postRequest->get( 'address_zip', '' ) )
			->setPayerAddressCity( $postRequest->get( 'address_city', '' ) )
			->setPayerAddressCountryCode( $postRequest->get( 'address_country_code', '' ) )
			->setPayerAddressStatus( $postRequest->get( 'address_status', '' ) )
			->setInternalId( (int)$postRequest->get( 'item_number', 0 ) )
			->setCurrencyCode( $postRequest->get( 'mc_currency', '' ) )
			->setTransactionFee( $postRequest->get( 'mc_fee', '0' ) ) // No Euro class to avoid exceptions on fees < 0
			->setAmountGross( Euro::newFromString( $postRequest->get( 'mc_gross', '0' ) ) )
			->setSettleAmount( Euro::newFromString( $postRequest->get( 'settle_amount', '0' ) ) )
			->setPaymentTimestamp( $postRequest->get( 'payment_date', '' ) )
			->setPaymentStatus( $postRequest->get( 'payment_status', '' ) )
			->setPaymentType( $postRequest->get( 'payment_type', '' ) );
	}

	private function createErrorResponse( PayPalPaymentNotificationVerifierException $e ): Response {
		switch ( $e->getCode() ) {
			case PayPalPaymentNotificationVerifierException::ERROR_WRONG_RECEIVER:
				return new Response( $e->getMessage(), Response::HTTP_FORBIDDEN );
			case PayPalPaymentNotificationVerifierException::ERROR_VERIFICATION_FAILED:
				return new Response( $e->getMessage(), Response::HTTP_FORBIDDEN );
			case PayPalPaymentNotificationVerifierException::ERROR_UNSUPPORTED_CURRENCY:
				return new Response( $e->getMessage(), Response::HTTP_NOT_ACCEPTABLE );
			default:
				return new Response( $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR );
		}
	}

	private function logResponseIfNeeded( PaypalNotificationResponse $response, Request $request ) {
		if ( $response->notificationWasHandled() ) {
			return;
		}

		$context = $response->getContext();
		$message = $context['message'] ?? self::MSG_NOT_HANDLED;
		$logLevel = $response->hasErrors() ? LogLevel::ERROR : LogLevel::INFO;
		unset( $context['message'] );
		$context['post_vars'] = $request->request->all();
		$this->ffFactory->getPaypalLogger()->log( $logLevel, $message, $context );
	}

}
