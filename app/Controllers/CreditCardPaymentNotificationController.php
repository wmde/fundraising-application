<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Euro\Euro;
use WMDE\Fundraising\DonationContext\UseCases\CreditCardPaymentNotification\CreditCardPaymentNotificationRequest;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * @license GNU GPL v2+
 */
class CreditCardPaymentNotificationController {

	public function handleNotification( FunFunFactory $ffFactory, Request $request ): Response {
		$donationId = $request->query->get( 'donation_id', '' );

		$response = $ffFactory->newCreditCardNotificationUseCase( $request->query->get( 'utoken', '' ) )
			->handleNotification(
				( new CreditCardPaymentNotificationRequest() )
					->setTransactionId( $request->query->get( 'transactionId', '' ) )
					->setDonationId( (int)$donationId )
					->setAmount( Euro::newFromCents( (int)$request->query->get( 'amount' ) ) )
					->setCustomerId( $request->query->get( 'customerId', '' ) )
					->setSessionId( $request->query->get( 'sessionId', '' ) )
					->setAuthId( $request->query->get( 'auth', '' ) )
					->setTitle( $request->query->get( 'title', '' ) )
					->setCountry( $request->query->get( 'country', '' ) )
					->setCurrency( $request->query->get( 'currency', '' ) )
			);

		$loggingContext = $response->getLowLevelError() === null ? [] : [ 'exception' => $response->getLowLevelError() ];
		if ( !$response->isSuccessful() ) {
			$ffFactory->getLogger()->error( 'Credit Card Notification Error: ' . $response->getErrorMessage(), $loggingContext );
		} elseif ( $loggingContext !== [] ) {
			$ffFactory->getLogger()->warning( 'Failed to send conformation email for credit card notification', $loggingContext );
		}

		return new Response( $ffFactory->newCreditCardNotificationPresenter()->present(
			$response,
			$donationId,
			$request->query->get( 'token', '' )
		) );
	}

}