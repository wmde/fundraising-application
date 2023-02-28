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
 */
class HandleExceptions implements EventSubscriberInterface {

	private const PRIORITY = -8;

	private FunFunFactory $presenterFactory;

	public function __construct( FunFunFactory $presenterFactory ) {
		$this->presenterFactory = $presenterFactory;
	}

	/**
	 * @return array<string, array{string, int}>
	 */
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
			$event->setResponse( new JsonResponse(
				[ 'ERR' => $event->getThrowable()->getMessage() ],
				Response::HTTP_NOT_FOUND,
				[ 'X-Status-Code' => Response::HTTP_NOT_FOUND ]
			) );
			return;
		}

		$event->setResponse( new Response(
			$this->presenterFactory->newPageNotFoundHtmlPresenter()->present(),
			Response::HTTP_NOT_FOUND,
			[ 'X-Status-Code' => Response::HTTP_NOT_FOUND ]
		) );
	}

	private function createInternalErrorResponse( ExceptionEvent $event ): void {
		$exception = $event->getThrowable();
		if ( $this->isJsonRequest( $event ) ) {
			$event->setResponse( new JsonResponse(
				[ 'ERR' => $exception->getMessage() ],
				// TODO check if we can return $code (see below) instead of 200
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
