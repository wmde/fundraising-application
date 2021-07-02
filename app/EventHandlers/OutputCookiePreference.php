<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\EventHandlers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Modify all HTML responses, adding the current consent state as a data attribute.
 *
 * This helps us keeping the consent cookie HTTP-only.
 *
 * Manipulating the HTML output directly, relying on an existing attribute, is
 * brittle, but until we have a better way for rendering our application shell
 * (see https://phabricator.wikimedia.org/T248460 ), this will have to do.
 */
class OutputCookiePreference implements EventSubscriberInterface {

	public static function getSubscribedEvents() {
		return [
			KernelEvents::RESPONSE => [ 'addCookiePreference' ]
		];
	}

	public function addCookiePreference( ResponseEvent $event ): void {
		if ( !$event->isMainRequest() ) {
			return;
		}

		$cookieConsent = $event->getRequest()->cookies->get( 'cookie_consent', 'unset' );
		$response = $event->getResponse();

		$response->setContent( str_replace(
			'data-application-vars=',
			'data-cookie-consent="' . $cookieConsent . '" data-application-vars=',
			$response->getContent()
		) );
	}
}
