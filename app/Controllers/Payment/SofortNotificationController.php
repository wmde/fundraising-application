<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Payment;

use DateTime;
use DateTimeInterface;
use Psr\Log\LogLevel;
use Sofort\SofortLib\Notification;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;
use WMDE\Fundraising\DonationContext\UseCases\BookDonationUseCase\BookDonationUseCase;
use WMDE\Fundraising\DonationContext\UseCases\NotificationRequest;
use WMDE\Fundraising\DonationContext\UseCases\NotificationResponse;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class SofortNotificationController {

	private FunFunFactory $ffFactory;
	private Request $request;

	public function index( FunFunFactory $ffFactory, Request $request ): Response {
		$this->ffFactory = $ffFactory;
		$this->request = $request;

		try {
			$useCaseRequest = $this->newUseCaseRequest();
			$response = $this->newUseCase()->handleNotification( $useCaseRequest );
		} catch ( UnexpectedValueException $e ) {
			$this->logException( $e );
			return new Response( 'Bad request', Response::HTTP_BAD_REQUEST );
		} catch ( \Exception $e ) {
			$this->logException( $e );
			return new Response( 'Error', Response::HTTP_INTERNAL_SERVER_ERROR );
		}

		$this->logResponseIfNeeded( $response );

		if ( $response->donationWasNotFound() ) {
			return new Response( 'Error', Response::HTTP_INTERNAL_SERVER_ERROR );
		}

		if ( $response->notificationWasHandled() ) {
			return new Response( 'Ok', Response::HTTP_OK );
		}

		return new Response( 'Bad request', Response::HTTP_BAD_REQUEST );
	}

	private function newUseCase(): BookDonationUseCase {
		return $this->ffFactory->newBookDonationUseCase(
			$this->request->query->get( 'updateToken', '' )
		);
	}

	private function newUseCaseRequest(): NotificationRequest {
		$vendorNotification = new Notification();
		$transactionId = $vendorNotification->getNotification( $this->request->getContent() );
		$time = $vendorNotification->getTime();
		$timeIsFormattedCorrectly = DateTime::createFromFormat( DateTimeInterface::ATOM, $vendorNotification->getTime() ?? '' ) !== false;

		if ( !$transactionId || !$time || !$timeIsFormattedCorrectly ) {
			throw new UnexpectedValueException( 'Invalid notification request' );
		}

		return new NotificationRequest(
			[
				'transactionId' => $transactionId,
				'valuationDate' => $time
			],
			$this->request->query->getInt( 'id' )
		);
	}

	private function logResponseIfNeeded( NotificationResponse $response ): void {
		if ( $response->notificationWasHandled() ) {
			return;
		}

		$this->ffFactory->getSofortLogger()->log(
			$response->hasErrors() ? LogLevel::ERROR : LogLevel::INFO,
			$response->getMessage() ?? 'Sofort request not handled',
			$this->getRequestVars()
		);
	}

	/**
	 * @return array<string,mixed>
	 */
	private function getRequestVars(): array {
		return [
			'request_content' => $this->request->getContent(),
			'query_vars' => $this->request->query->all()
		];
	}

	private function logException( UnexpectedValueException|\Exception $e ): void {
		$this->ffFactory->getSofortLogger()->log(
			LogLevel::ERROR,
			'An Exception happened: ' . $e->getMessage(),
			array_merge( $this->getRequestVars(), [ 'stacktrace' => $e->getTraceAsString() ] )
		);
	}
}
