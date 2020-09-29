<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\EventHandlers;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use WMDE\Fundraising\Frontend\App\AccessDeniedException;

class LogErrors implements EventSubscriberInterface {

	private const PRIORITY = -2;

	private LoggerInterface $logger;

	public function __construct( LoggerInterface $logger ) {
		$this->logger = $logger;
	}

	public static function getSubscribedEvents() {
		return [
			KernelEvents::EXCEPTION => [ 'onKernelException', self::PRIORITY ]
		];
	}

	public function onKernelException( GetResponseForExceptionEvent $event ): void {
		$exception = $event->getException();

		if ( $exception instanceof AccessDeniedException || $exception instanceof NotFoundHttpException ) {
			return;
		}

		$request = $event->getRequest();
		$this->logger->error(
			$exception->getMessage(),
			[
				'code' => $exception->getCode(),
				'file' => $exception->getFile(),
				'line' => $exception->getLine(),
				'stack_trace' => $exception->getTraceAsString(),
				'referrer' => $request->headers->get( 'referer' ),
				'uri' => $request->getRequestUri(),
				'languages' => $request->getLanguages(),
				'charsets' => $request->getCharsets(),
				'content_types' => $request->getAcceptableContentTypes(),
				'method' => $request->getMethod(),
				// Errbit uses this property to generate stack tracke
				'exception' => $exception
			]
		);
	}

}
