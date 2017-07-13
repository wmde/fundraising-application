<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\RouteHandlers;

use DateTime;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\PaymentContext\RequestModel\SofortNotificationRequest;
use WMDE\Fundraising\Frontend\PaymentContext\ResponseModel\SofortNotificationResponse;

class SofortNotificationHandler {

	private $ffFactory;

	public function __construct( FunFunFactory $ffFactory ) {
		$this->ffFactory = $ffFactory;
	}

	public function handle( Request $request ): Response {
		$useCase = $this->ffFactory->newHandleSofortPaymentNotificationUseCase( $request->query->get( 'updateToken' ) );

		$response = $useCase->handleNotification( $this->newUseCaseRequestFromRequest( $request ) );

		$this->logResponseIfNeeded( $response, $request );

		if ( !$response->notificationWasHandled() ) {
			return new Response( '', Response::HTTP_BAD_REQUEST );
		} elseif ( $response->hasErrors() ) {
			return new Response( '', Response::HTTP_INTERNAL_SERVER_ERROR );
		}

		return new Response( '', Response::HTTP_OK );
	}

	private function newUseCaseRequestFromRequest( Request $request ): SofortNotificationRequest {
		$usecaseRequest = new SofortNotificationRequest();
		$usecaseRequest->setDonationId( $request->query->getInt( 'id' ) );
		$usecaseRequest->setTransactionId( $request->request->get( 'transaction', '' ) );
		$time = DateTime::createFromFormat( DateTime::ATOM, $request->request->get( 'time', '' ) );
		if ( $time instanceof DateTime ) {
			$usecaseRequest->setTime( $time );
		}

		return $usecaseRequest;
	}

	private function logResponseIfNeeded( SofortNotificationResponse $response, Request $request ) {
		if ( $response->notificationWasHandled() ) {
			return;
		}

		$context = $response->getContext();
		$message = $context['message'] ?? 'Sofort request not handled';
		$logLevel = $response->hasErrors() ? LogLevel::ERROR : LogLevel::INFO;
		unset( $context['message'] );
		$context['post_vars'] = array_merge( $request->query->all(), $request->request->all() );
		$this->ffFactory->getSofortLogger()->log( $logLevel, $message, $context );
	}

}
