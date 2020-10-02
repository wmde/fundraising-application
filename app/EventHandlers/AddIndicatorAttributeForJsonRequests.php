<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\EventHandlers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Add indicator attribute `request_stack.is_json` to request for JSON/JSONP requests,
 * based on the HTTP `Accept` (accepted content type) header.
 *
 * @todo When https://phabricator.wikimedia.org/T263436 is done, use Symfony's "_format" parameter instead,
 *       see https://symfony.com/doc/current/routing.html#special-parameters
 */
class AddIndicatorAttributeForJsonRequests implements EventSubscriberInterface {

	private const PRIORITY = 64;
	public const REQUEST_IS_JSON_ATTRIBUTE = 'request_stack.is_json';

	public static function getSubscribedEvents() {
		return [
			// Priority needs to be higher than the one for error handling interception,
			// to be able to recognize JSON requests when creating an error handling response
			KernelEvents::REQUEST => [ 'onKernelRequest', self::PRIORITY ],
		];
	}

	public function onKernelRequest( KernelEvent $event ): void {
		$request = $event->getRequest();
		if ( $this->isJsonRequest( $request ) || $this->isJsonPRequest( $request ) ) {
			$request->attributes->set( self::REQUEST_IS_JSON_ATTRIBUTE, true );
		}
	}

	private function isJsonRequest( Request $request ): bool {
		return in_array( 'application/json', $request->getAcceptableContentTypes() );
	}

	private function isJsonPRequest( Request $request ): bool {
		return in_array( 'application/javascript', $request->getAcceptableContentTypes() )
			&& $request->get( 'callback', null );
	}
}
