<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\RouteHandlers;

use DateTime;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\SofortPaymentNotification\SofortPaymentNotificationUseCase;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\PaymentContext\RequestModel\SofortNotificationRequest;
use WMDE\Fundraising\Frontend\PaymentContext\ResponseModel\SofortNotificationResponse;

class SofortNotificationHandler {

	private $ffFactory;

	/**
	 * @var Request
	 */
	private $request;

	public function __construct( FunFunFactory $ffFactory ) {
		$this->ffFactory = $ffFactory;
	}

	public function handle( Request $request ): Response {
		$this->request = $request;

		$useCaseRequest = $this->newUseCaseRequest();

		if ( $useCaseRequest === null ) {
			$this->logWebRequest( [ 'message' => 'Bad notification time' ], LogLevel::ERROR );
			return new Response( 'Bad request', Response::HTTP_BAD_REQUEST );
		}

		$response = $this->newUseCase()->handleNotification( $useCaseRequest );

		$this->logResponseIfNeeded( $response );

		if ( $response->hasErrors() ) {
			return new Response( 'Error', Response::HTTP_INTERNAL_SERVER_ERROR );
		}

		if ( $response->notificationWasHandled() ) {
			return new Response( 'Ok', Response::HTTP_OK );
		}

		return new Response( 'Bad request', Response::HTTP_BAD_REQUEST );
	}

	private function newUseCase(): SofortPaymentNotificationUseCase {
		return $this->ffFactory->newHandleSofortPaymentNotificationUseCase( $this->request->query->get( 'updateToken' ) );
	}

	private function newUseCaseRequest(): ?SofortNotificationRequest {
		$time = $this->getTimeFromRequest();

		if ( $time === false ) {
			return null;
		}

		$useCaseRequest = new SofortNotificationRequest();

		$useCaseRequest->setTime( $time );
		$useCaseRequest->setDonationId( $this->request->query->getInt( 'id' ) );
		$useCaseRequest->setTransactionId( $this->request->request->get( 'transaction', '' ) );

		return $useCaseRequest;
	}

	private function getTimeFromRequest() {
		return DateTime::createFromFormat( DateTime::ATOM, $this->request->request->get( 'time', '' ) );
	}

	private function logResponseIfNeeded( SofortNotificationResponse $response ) {
		if ( $response->notificationWasHandled() ) {
			return;
		}

		$this->logWebRequest(
			$response->getContext(),
			$response->hasErrors() ? LogLevel::ERROR : LogLevel::INFO
		);
	}

	private function logWebRequest( array $context, string $logLevel ) {
		$message = $context['message'] ?? 'Sofort request not handled';
		unset( $context['message'] );

		$context['post_vars'] = $this->request->request->all();
		$context['query_vars'] = $this->request->query->all();
		$this->ffFactory->getSofortLogger()->log( $logLevel, $message, $context );
	}

}
