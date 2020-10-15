<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Payment;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Euro\Euro;
use WMDE\Fundraising\DonationContext\UseCases\CreditCardPaymentNotification\CreditCardNotificationResponse;
use WMDE\Fundraising\DonationContext\UseCases\CreditCardPaymentNotification\CreditCardPaymentNotificationRequest;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * @license GPL-2.0-or-later
 */
class CreditCardPaymentNotificationController {

	private const MSG_NOT_HANDLED = 'Credit card request "%s" not handled';

	public function handleNotification( FunFunFactory $ffFactory, Request $request ): Response {
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

		return new Response( $ffFactory->newCreditCardNotificationPresenter()->present(
			new CreditCardNotificationResponse(
				false,
				sprintf( 'Function "%s" not supported by this end point', $queryParams->get( 'function' ) )
			),
			$donationId,
			$queryParams->get( 'token', '' )
		) );
	}

	private function handleBillingNotification( FunFunFactory $ffFactory, ParameterBag $queryParams, string $donationId, string $clientIp ): Response {
		$response = $ffFactory->newCreditCardNotificationUseCase( $queryParams->get( 'utoken', '' ) )
			->handleNotification(
				( new CreditCardPaymentNotificationRequest() )
					->setTransactionId( $queryParams->get( 'transactionId', '' ) )
					->setDonationId( (int)$donationId )
					->setAmount( Euro::newFromCents( (int)$queryParams->get( 'amount' ) ) )
					->setCustomerId( $queryParams->get( 'customerId', '' ) )
					->setSessionId( $queryParams->get( 'sessionId', '' ) )
					->setAuthId( $queryParams->get( 'auth', '' ) )
					->setTitle( $queryParams->get( 'title', '' ) )
					->setCountry( $queryParams->get( 'country', '' ) )
					->setCurrency( $queryParams->get( 'currency', '' ) )
			);

		$loggingContext = $response->getLowLevelError() === null ? [] : [ 'exception' => $response->getLowLevelError() ];
		if ( !$response->isSuccessful() ) {
			$loggingContext['queryParams'] = $queryParams->all();
			$loggingContext['clientIP'] = $clientIp;
			$ffFactory->getLogger()->error( 'Credit Card Notification Error: ' . $response->getErrorMessage(), $loggingContext );
		} elseif ( $loggingContext !== [] ) {
			$ffFactory->getLogger()->warning( 'Failed to send conformation email for credit card notification', $loggingContext );
		}

		return new Response( $ffFactory->newCreditCardNotificationPresenter()->present(
			$response,
			$donationId,
			$queryParams->get( 'token', '' )
		) );
	}

}
