<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\EventHandlers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use WMDE\Fundraising\Frontend\App\AccessDeniedException;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * Generate different error responses for the different exceptions.
 *
 */
class HandleExceptions implements EventSubscriberInterface {

	private const PRIORITY = -8;

	private FunFunFactory $presenterFactory;

	public function __construct( FunFunFactory $presenterFactory ) {
		$this->presenterFactory = $presenterFactory;
	}

	public static function getSubscribedEvents(): array {
		return [
			KernelEvents::EXCEPTION => [ 'onKernelException', self::PRIORITY ]
		];
	}

	public function onKernelException( ExceptionEvent $event ): void {
		$exception = $event->getThrowable();
		switch ( true ) {
			case $exception instanceof AccessDeniedException:
				$this->createAccessDeniedResponse( $event );
				break;
			case $exception instanceof NotFoundHttpException:
				$this->createNotFoundResponse( $event );
				break;
			default:
				$this->createInternalErrorResponse( $event );
		}
	}

	private function createAccessDeniedResponse( ExceptionEvent $event ): void {
		$event->setResponse( new Response(
			$this->presenterFactory->newAccessDeniedHtmlPresenter()->present(
				$event->getThrowable()->getMessage()
			),
			403,
			[ 'X-Status-Code' => 403 ]
		) );
	}

	private function createNotFoundResponse( ExceptionEvent $event ): void {
		if ( $this->isJsonRequest( $event ) ) {
			$event->setResponse( JsonResponse::create(
				[ 'ERR' => $event->getThrowable()->getMessage() ],
				404,
				[ 'X-Status-Code' => 404 ]
			) );
			return;
		}

		$event->setResponse( new Response(
			$this->presenterFactory->newPageNotFoundHtmlPresenter()->present(),
			404,
			[ 'X-Status-Code' => 404 ]
		) );
	}

	private function createInternalErrorResponse( ExceptionEvent $event ) {
		$exception = $event->getThrowable();
		if ( $this->isJsonRequest( $event ) ) {
			$event->setResponse( JsonResponse::create(
				[ 'ERR' => $exception->getMessage() ]
			) );
			return;
		}

		$code = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
		$event->setResponse( new Response(
			$this->presenterFactory->getInternalErrorHtmlPresenter()->present( $exception ),
			$code
		) );
	}

	private function isJsonRequest( ExceptionEvent $event ): bool {
		return $event->getRequest()
			->attributes
			->get( AddIndicatorAttributeForJsonRequests::REQUEST_IS_JSON_ATTRIBUTE, false );
	}

}
