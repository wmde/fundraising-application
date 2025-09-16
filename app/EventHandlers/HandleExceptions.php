<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\EventHandlers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use WMDE\Fundraising\Frontend\App\AccessDeniedException;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * Generate different error responses for the different exceptions.
 */
class HandleExceptions implements EventSubscriberInterface {

	private const PRIORITY = -8;

	public function __construct( private readonly FunFunFactory $presenterFactory ) {
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
		$code = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
		if ( $this->isJsonRequest( $event ) ) {
			$responseJSON = [ 'ERR' => $exception->getMessage() ];
			$responseJSON = $this->modifyErrorResponseForSymfonyValidationErrors( $exception, $responseJSON );

			// TODO check if all of our JavaScript API-call code can process return codes other than 200, if yes, pass $code as 2nd parameter
			$event->setResponse( new JsonResponse( $responseJSON ) );
			return;
		}

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

	/**
	 * When using the `#[MapRequestPayload]` attribute, Symfony will throw a `ValidationFailedException` if the JSON
	 * sent by the client does not match the expected class definition (e.g. missing field, fields of wrong
	 * type, etc). This commit adds an `validationErrors` field to the resulting JSON with `field name => error` pairs
	 * to help the developer debug the input.
	 *
	 * @param \Throwable $exception
	 * @param array<string,string> $responseJSON
	 * @return array<string,mixed>
	 */
	private function modifyErrorResponseForSymfonyValidationErrors( \Throwable $exception, array $responseJSON ) {
		$previous = $exception->getPrevious();
		if ( $exception instanceof HttpException && $previous instanceof ValidationFailedException ) {
			$validationErrors = [];
			$errorText = '';
			foreach ( $previous->getViolations() as $violation ) {
				$validationErrors[$violation->getPropertyPath()] = $violation->getMessage();
				$errorText .= sprintf( "%s: %s\n", $violation->getPropertyPath(), $violation->getMessage() );
			}
			$responseJSON['ERR'] = $errorText;
			$responseJSON['validationErrors'] = $validationErrors;
		}
		return $responseJSON;
	}

}
