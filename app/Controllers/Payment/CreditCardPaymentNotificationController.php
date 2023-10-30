<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Payment;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\DonationContext\UseCases\NotificationRequest;
use WMDE\Fundraising\DonationContext\UseCases\NotificationResponse;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class CreditCardPaymentNotificationController {

	private const MSG_NOT_HANDLED = 'Credit card request "%s" not handled';
	private const MSG_NOT_SUPPORTED = 'Function "%s" not supported by this end point';
	private const MSG_ERROR = 'Credit Card Notification Error: %s';
	private const MSG_CRITICAL = 'An Exception happened: %s';

	public function index( FunFunFactory $ffFactory, Request $request ): Response {
		$queryParams = $request->query;
		$donationId = $queryParams->get( 'donation_id', '' );

		if ( !$this->requestIsForPaymentCompletion( $queryParams ) ) {
			return $this->handleInvalidNotificationType( $ffFactory, $queryParams, $donationId );
		}

		return $this->handleBillingNotification( $ffFactory, $queryParams, $donationId, $request->getClientIp() );
	}

	private function requestIsForPaymentCompletion( ParameterBag $query ): bool {
		return $query->get( 'function', '' ) === 'billing';
	}

	private function handleInvalidNotificationType( FunFunFactory $ffFactory, ParameterBag $queryParams, string $donationId ): Response {
		$ffFactory->getCreditCardLogger()->info(
			sprintf( self::MSG_NOT_HANDLED, $queryParams->get( 'function' ) ),
			$queryParams->all()
		);

		return new Response(
			$ffFactory->newCreditCardNotificationPresenter()->present(
				NotificationResponse::newFailureResponse(
					sprintf( self::MSG_NOT_SUPPORTED, $queryParams->get( 'function' ) )
				),
				$donationId,
				$queryParams->get( 'token', '' )
			)
		);
	}

	private function handleBillingNotification( FunFunFactory $ffFactory, ParameterBag $queryParams, string $donationId, string $clientIp ): Response {
		try {
			$response = $ffFactory->newBookDonationUseCase( $queryParams->get( 'utoken', '' ) )
				->handleNotification( new NotificationRequest( $queryParams->all(), intval( $donationId ) ) );
		} catch ( \Exception $e ) {
			$ffFactory->getLogger()->critical(
				sprintf( self::MSG_CRITICAL, $e->getMessage() ),
				[ 'stacktrace' => $e->getTraceAsString() ]
			);
			return new Response( $e->getMessage(), 500 );
		}

		if ( $response->hasErrors() ) {
			$ffFactory->getLogger()->error(
				sprintf( self::MSG_ERROR, $response->getMessage() ),
				[ 'queryParams' => $queryParams->all(), 'clientIP' => $clientIp ]
			);
		}

		return new Response(
			$ffFactory->newCreditCardNotificationPresenter()->present(
				$response,
				$donationId,
				$queryParams->get( 'token', '' )
			)
		);
	}

}
