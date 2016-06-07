<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\RouteHandlers;

use Silex\Application;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Domain\Model\Euro;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\UseCases\CreditCardPaymentNotification\CreditCardPaymentNotificationRequest;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CreditCardNotificationHandler {

	private $ffFactory;

	public function __construct( FunFunFactory $ffFactory ) {
		$this->ffFactory = $ffFactory;
	}

	public function handle( Request $request ): Response {
		$useCase = $this->ffFactory->newCreditCardNotificationUseCase( $request->request->get( 'utoken', '' ) );

		$success = $useCase->handleNotification( $this->newUseCaseRequestFromPost( $request->request ) );

		return new Response( $success ? 'successful' : 'failed' );
	}

	private function newUseCaseRequestFromPost( ParameterBag $postRequest ): CreditCardPaymentNotificationRequest {
		return ( new CreditCardPaymentNotificationRequest() )
			->setTransactionId( $postRequest->get( 'transactionId', '' ) )
			->setDonationId( (int)$postRequest->get( 'donation_id', '' ) )
			->setAmount( Euro::newFromCents( (int)$postRequest->get( 'amount' ) ) )
			->setCustomerId( $postRequest->get( 'customerId', '' ) )
			->setSessionId( $postRequest->get( 'sessionId', '' ) )
			->setAuthId( $postRequest->get(  'auth', '' ) )
			->setTitle( $postRequest->get( 'title', '' ) )
			->setCountry( $postRequest->get( 'country', '' ) )
			->setCurrency( $postRequest->get( 'currency', '' ) );
	}

}