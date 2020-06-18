<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers;

use Doctrine\ORM\Query\Parameter;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\Payment\PayPalPaymentNotificationVerifierException;
use WMDE\Fundraising\MembershipContext\UseCases\HandleSubscriptionSignupNotification\SubscriptionSignupRequest;
use WMDE\Fundraising\PaymentContext\ResponseModel\PaypalNotificationResponse;
use WMDE\Fundraising\PaymentContext\RequestModel\PayPalPaymentNotificationRequest;

class PaypalNotificationControllerForMembershipFee {

	const TYPE_SUBSCRIPTION_SIGNUP = 'subscr_signup';
	const TYPE_SUBSCRIPTION_PAYMENT = 'subscr_payment';


	public function handle( FunFunFactory $ffFactory, Request $request ): Response {
		$post = $request->request;

		try {
			$ffFactory->getPayPalMembershipFeeNotificationVerifier()->verify( $post->all() );
		} catch ( PayPalPaymentNotificationVerifierException $e ) {
			$ffFactory->getPaypalLogger()->log( LogLevel::ERROR, $e->getMessage(), [
				'post_vars' => $post->all()
			] );
			return $this->createErrorResponse( $e );
		}

		switch ( $post->get( 'txn_type' ) ) {
			case self::TYPE_SUBSCRIPTION_SIGNUP:
				$useCase = $ffFactory->newMembershipApplicationSubscriptionSignupNotificationUseCase( $this->getUpdateToken( $post ) );
				$response = $useCase->handleNotification( $this->newSubscriptionSignupRequestFromPost( $post ) );
				break;
			case self::TYPE_SUBSCRIPTION_PAYMENT:
				$useCase = $ffFactory->newMembershipApplicationSubscriptionPaymentNotificationUseCase( $this->getUpdateToken( $post ) );
				$response = $useCase->handleNotification( $this->newSubscriptionPaymentRequestFromPost( $post ) );
				break;
			default:
				$response = PaypalNotificationResponse::newUnhandledResponse( [ 'message' => 'unsupported transaction type' ] );
				break;
		}

		$this->logResponseIfNeeded( $ffFactory, $response, $post );

		return new Response( '', Response::HTTP_OK ); # PayPal expects an empty response
	}

	private function getUpdateToken( ParameterBag $postRequest ): string {
		return $this->getValueFromCustomVars( $postRequest->get( 'custom', '' ), 'utoken' );
	}

	private function getValueFromCustomVars( string $customVars, string $key ): string {
		$vars = json_decode( $customVars, true );
		return !empty( $vars[$key] ) ? $vars[$key] : '';
	}

	private function newSubscriptionSignupRequestFromPost( ParameterBag $postRequest ): SubscriptionSignupRequest {
		return ( new SubscriptionSignupRequest() )
			->setTransactionType( $postRequest->get( 'txn_type', '' ) )
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
			->setPayerAddressCountry( $postRequest->get( 'address_country_code', '' ) )
			->setPayerAddressStatus( $postRequest->get( 'address_status', '' ) )
			->setApplicationId( (int)$postRequest->get( 'item_number', 0 ) )
			->setPaymentType( $postRequest->get( 'payment_type', '' ) )
			->setCurrencyCode( $postRequest->get( 'mc_currency', '' ) );
	}

	private function newSubscriptionPaymentRequestFromPost( ParameterBag $postRequest ): PayPalPaymentNotificationRequest {
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

	private function logResponseIfNeeded( FunFunFactory $ffFactory, PaypalNotificationResponse $response, ParameterBag $post ) {
		if ( $response->notificationWasHandled() ) {
			return;
		}

		$context = $response->getContext();
		$message = $context['message'] ?? 'Paypal request not handled';
		$logLevel = $response->hasErrors() ? LogLevel::ERROR : LogLevel::INFO;
		unset( $context['message'] );
		$context['post_vars'] = $post->all();
		$ffFactory->getPaypalLogger()->log( $logLevel, $message, $context );
	}

}