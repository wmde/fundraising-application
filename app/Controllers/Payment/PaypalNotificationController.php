<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Payment;

use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\Payment\PayPalPaymentNotificationVerifierException;
use WMDE\Fundraising\PaymentContext\RequestModel\PayPalPaymentNotificationRequest;
use WMDE\Fundraising\PaymentContext\ResponseModel\PaypalNotificationResponse;

class PaypalNotificationController {

	private const MSG_NOT_HANDLED = 'PayPal request not handled';
	private const PAYPAL_LOG_FILTER = [ 'payer_email', 'payer_id' ];

	public function index( FunFunFactory $ffFactory, Request $request ): Response {
		$post = $request->request;

		if ( !$this->requestIsForPaymentCompletion( $post ) ) {
			$ffFactory->getPaypalLogger()->log( LogLevel::INFO, self::MSG_NOT_HANDLED, [ 'post_vars' => $post->all() ] );
			return new Response( '', Response::HTTP_OK );
		}

		try {
			$ffFactory->getPayPalPaymentNotificationVerifier()->verify( $post->all() );
		} catch ( PayPalPaymentNotificationVerifierException $e ) {
			$parametersToLog = $post->all();
			foreach ( self::PAYPAL_LOG_FILTER as $remove ) {
				unset( $parametersToLog[$remove] );
			}
			$ffFactory->getPaypalLogger()->log( LogLevel::ERROR, $e->getMessage(), [
				'post_vars' => $parametersToLog
			] );
			return $this->createErrorResponse( $e );
		}

		$useCase = $ffFactory->newHandlePayPalPaymentCompletionNotificationUseCase( $this->getUpdateToken( $post ) );

		$response = $useCase->handleNotification( $this->newUseCaseRequestFromPost( $post ) );
		$this->logResponseIfNeeded( $ffFactory, $response, $post );

		// PayPal expects an empty response
		return new Response( '', Response::HTTP_OK );
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
		// we're not using Euro class for amounts to avoid exceptions on fees or other fields where the value is < 0
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
			->setInternalId( $this->getDonationId( $postRequest ) )
			->setCurrencyCode( $postRequest->get( 'mc_currency', '' ) )
			->setTransactionFee( $postRequest->get( 'mc_fee', '0' ) )
			->setAmountGross( Euro::newFromString( $postRequest->get( 'mc_gross', '0' ) ) )
			->setSettleAmount( Euro::newFromString( $postRequest->get( 'settle_amount', '0' ) ) )
			->setPaymentTimestamp( $postRequest->get( 'payment_date', '' ) )
			->setPaymentStatus( $postRequest->get( 'payment_status', '' ) )
			->setPaymentType( $postRequest->get( 'payment_type', '' ) );
	}

	private function getDonationId( ParameterBag $postRequest ): int {
		$itemId = $postRequest->getInt( 'item_number', 0 );
		if ( $itemId ) {
			return $itemId;
		}

		return (int)$this->getValueFromCustomVars( $postRequest->get( 'custom', '' ), 'sid' );
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

	private function logResponseIfNeeded( FunFunFactory $ffFactory, PaypalNotificationResponse $response, ParameterBag $post ) {
		if ( $response->notificationWasHandled() ) {
			return;
		}

		$context = $response->getContext();
		$message = $context['message'] ?? self::MSG_NOT_HANDLED;
		$logLevel = $response->hasErrors() ? LogLevel::ERROR : LogLevel::INFO;
		unset( $context['message'] );
		$context['post_vars'] = $post->all();
		$ffFactory->getPaypalLogger()->log( $logLevel, $message, $context );
	}

}
