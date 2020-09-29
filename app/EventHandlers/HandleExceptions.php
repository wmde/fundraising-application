<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\EventHandlers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use WMDE\Fundraising\Frontend\App\AccessDeniedException;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * Generate different error responses for the different exceptions.
 *
 * @todo Replace this class with a custom error controller when we have switched to Symfony
 *       see https://phabricator.wikimedia.org/T263436
 *       see https://symfony.com/doc/current/controller/error_pages.html#overriding-the-default-errorcontroller
 */
class HandleExceptions implements EventSubscriberInterface {

	private const PRIORITY = -8;

	private FunFunFactory $presenterFactory;

	public function __construct( FunFunFactory $presenterFactory ) {
 $this->presenterFactory = $presenterFactory;
	}

	public static function getSubscribedEvents() {
		return [
			KernelEvents::EXCEPTION => [ 'onKernelException', self::PRIORITY ]
		];
	}

	public function onKernelException( GetResponseForExceptionEvent $event ): void {
		$exception = $event->getException();
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

	private function createAccessDeniedResponse( GetResponseForExceptionEvent $event ): void {
		$event->setResponse( new Response(
			$this->presenterFactory->newAccessDeniedHtmlPresenter()->present(
				$event->getException()->getMessage()
			),
			403,
			[ 'X-Status-Code' => 403 ]
		) );
	}

	private function createNotFoundResponse( GetResponseForExceptionEvent $event ): void {
		if ( $this->isJsonRequest( $event ) ) {
			$event->setResponse( JsonResponse::create(
				[ 'ERR' => $event->getException()->getMessage() ],
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

	private function createInternalErrorResponse( GetResponseForExceptionEvent $event ) {
		$exception = $event->getException();
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

	private function isJsonRequest( GetResponseForExceptionEvent $event ): bool {
		return $event->getRequest()
			->attributes
			->get( AddIndicatorAttributeForJsonRequests::REQUEST_IS_JSON_ATTRIBUTE, false );
	}

}
