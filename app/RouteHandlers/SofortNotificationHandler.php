<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\RouteHandlers;

use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\PaymentContext\ResponseModel\PaypalNotificationResponse;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\PayPalPaymentNotificationVerifierException;
use WMDE\Fundraising\Frontend\PaymentContext\RequestModel\SofortPaymentNotificationRequest;
use DateTime;

class SofortNotificationHandler {

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

		$useCase = $this->ffFactory->newHandlePayPalPaymentNotificationUseCase( $this->getUpdateToken( $post ) );

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

	private function newUseCaseRequestFromPost( ParameterBag $postRequest ): SofortPaymentNotificationRequest {
		return ( new SofortPaymentNotificationRequest() )
			->setTransactionId( $postRequest->get( 'transaction', '' ) )
			->setTime( new DateTime( $postRequest->get( 'time', '' ) ) );
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
		$message = $context['message'] ?? 'Paypal request not handled';
		$logLevel = $response->hasErrors() ? LogLevel::ERROR : LogLevel::INFO;
		unset( $context['message'] );
		$context['post_vars'] = $request->request->all();
		$this->ffFactory->getPaypalLogger()->log( $logLevel, $message, $context );
	}

}
