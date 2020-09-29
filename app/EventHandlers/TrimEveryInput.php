<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\EventHandlers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Remove leading and trailing whitespace from every string value in query (GET) and request (POST) parameters.
 */
class TrimEveryInput implements EventSubscriberInterface {

	private const PRIORITY = 512;

	public static function getSubscribedEvents() {
		return [
			KernelEvents::REQUEST => [ 'onKernelRequest', self::PRIORITY ],
		];
	}

	public function onKernelRequest( KernelEvent $event ): void {
		$request = $event->getRequest();
		foreach ( [ $request->request, $request->query ] as $parameterBag ) {
			$this->trimParameters( $parameterBag );
		}
	}

	private function trimParameters( ParameterBag $parameterBag ): void {
		foreach ( $parameterBag->keys() as $key ) {
			$value = $parameterBag->get( $key );
			if ( is_string( $value ) ) {
				$parameterBag->set( $key, trim( $value ) );
			}
		}
	}
}
