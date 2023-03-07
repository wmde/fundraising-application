<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\EventHandlers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PrettifyJsonResponse implements EventSubscriberInterface {

	/**
	 * @return array<string, string>
	 */
	public static function getSubscribedEvents(): array {
		return [
			KernelEvents::RESPONSE => 'onKernelResponse'
		];
	}

	public function onKernelResponse( ResponseEvent $event ): void {
		$response = $event->getResponse();
		if ( $response instanceof JsonResponse ) {
			$response->setEncodingOptions( JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		}
	}

}
