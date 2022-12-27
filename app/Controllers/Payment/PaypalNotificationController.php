<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Payment;

use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Euro\Euro;
use WMDE\Fundraising\DonationContext\UseCases\NotificationRequest;
use WMDE\Fundraising\DonationContext\UseCases\NotificationResponse;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\PaymentContext\Services\ExternalVerificationService\PayPal\PayPalVerificationService;

class PaypalNotificationController {

	private const MSG_NOT_HANDLED = 'PayPal request not handled';
	private const PAYPAL_LOG_FILTER = [ 'payer_email', 'payer_id' ];

	private const ALLOWED_TRANSACTION_TYPES = [
		// Regular one-time payment
		'web_accept',
		// Recurring payment ("subscription")
		'subscr_payment',
		// money sent directly via email
		'send_money',
	];

	public function index( FunFunFactory $ffFactory, Request $request ): Response {
		$post = $request->request;

		if ( !$this->requestIsForPaymentCompletion( $post ) ) {
			$ffFactory->getPaypalLogger()->log( LogLevel::INFO, self::MSG_NOT_HANDLED, [ 'post_vars' => $post->all() ] );
			return new Response( '', Response::HTTP_OK );
		}

		try {
			$useCase = $ffFactory->newBookDonationUseCase( $this->getUpdateToken( $post ) );
			$response = $useCase->handleNotification( new NotificationRequest(
				$post->all(),
				$this->getDonationId( $post )
			) );
			if ( $response->donationWasNotFound() ) {
				$response = $this->createAnonymousDonation( $ffFactory, $request );
			}
		} catch ( \Exception $e ) {
			$this->logError(
				$ffFactory,
				$post,
				'An Exception happened: ' . $e->getMessage(),
				[ 'stacktrace' => $e->getTraceAsString() ]
			);
			return new Response( $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR );
		}

		if ( $response->hasErrors() ) {
			return $this->logAndReturnErrorResponse( $response, $ffFactory, $post );
		}

		return $this->createSuccessResponse();
	}

	private function createAnonymousDonation( FunFunFactory $ffFactory, Request $request ): NotificationResponse {
		$useCase = $ffFactory->newHandlePaypalPaymentWithoutDonationUseCase();
		$amount = Euro::newFromString( $request->request->get( 'mc_gross', '0' ) );
		return $useCase->handleNotification( $amount->getEuroCents(), $request->request->all() );
	}

	private function logError( FunFunFactory $ffFactory, ParameterBag $post, string $message, array $additionalContext = [],
							   string $logLevel = LogLevel::ERROR ): void {
		$parametersToLog = $post->all();
		foreach ( self::PAYPAL_LOG_FILTER as $remove ) {
			unset( $parametersToLog[$remove] );
		}
		$ffFactory->getPaypalLogger()->log( $logLevel, $message, [
			'post_vars' => $parametersToLog,
			...$additionalContext
		] );
		$ffFactory->getLogger()->log( $logLevel, $message );
	}

	private function getUpdateToken( ParameterBag $postRequest ): string {
		return $this->getValueFromCustomVars( $postRequest->get( 'custom', '' ), 'utoken' );
	}

	private function getValueFromCustomVars( string $customVars, string $key ): string {
		$vars = json_decode( $customVars, true );
		return !empty( $vars[$key] ) ? strval( $vars[$key] ) : '';
	}

	private function requestIsForPaymentCompletion( ParameterBag $post ): bool {
		$transactionType = $post->get( 'txn_type', '' );
		$paymentStatus = $post->get( 'payment_status', '' );
		if ( !in_array( $transactionType, self::ALLOWED_TRANSACTION_TYPES ) ) {
			return false;
		}
		return $paymentStatus === 'Completed';
	}

	private function getDonationId( ParameterBag $postRequest ): int {
		$itemId = $postRequest->getInt( 'item_number', 0 );
		if ( $itemId ) {
			return $itemId;
		}

		return (int)$this->getValueFromCustomVars( $postRequest->get( 'custom', '' ), 'sid' );
	}

	private function logAndReturnErrorResponse( NotificationResponse $response, FunFunFactory $ffFactory, ParameterBag $post ): Response {
		// Avoid retries when the payment was already booked
		if ( $response->paymentWasAlreadyCompleted() ) {
			$this->logError( $ffFactory, $post, $response->getMessage(), [], LogLevel::WARNING );
			return $this->createSuccessResponse();
		}

		$this->logError( $ffFactory, $post, $response->getMessage() );
		return $this->createErrorResponse( $response->getMessage() );
	}

	private function createSuccessResponse(): Response {
		// PayPal expects an empty response body
		return new Response( '', Response::HTTP_OK );
	}

	private function createErrorResponse( string $message ): Response {
		if ( $this->messageIsErrorUnknown( $message ) ) {
			return new Response( $message, Response::HTTP_FORBIDDEN );
		}

		switch ( $message ) {
			case PayPalVerificationService::ERROR_WRONG_RECEIVER:
			case PayPalVerificationService::ERROR_UNCONFIRMED:
				return new Response( $message, Response::HTTP_FORBIDDEN );
			case PayPalVerificationService::ERROR_UNSUPPORTED_CURRENCY:
				return new Response( $message, Response::HTTP_NOT_ACCEPTABLE );
			default:
				return new Response( $message, Response::HTTP_INTERNAL_SERVER_ERROR );
		}
	}

	/**
	 * The ERROR_UNKNOWN message has the returned error in it meaning
	 * we need to check that the original string is in there
	 *
	 * @param string $message
	 *
	 * @return bool
	 */
	private function messageIsErrorUnknown( string $message ): bool {
		$unknownMessageSubstring = substr(
			PayPalVerificationService::ERROR_UNKNOWN,
			0,
			strpos( PayPalVerificationService::ERROR_UNKNOWN, "%s" )
		);

		return str_contains( $message, $unknownMessageSubstring );
	}
}
