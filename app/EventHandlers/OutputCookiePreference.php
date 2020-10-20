<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\EventHandlers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class OutputCookiePreference implements EventSubscriberInterface {

	public static function getSubscribedEvents() {
		return [
			KernelEvents::RESPONSE => [ 'addCookiePreference' ]
		];
	}

	public function addCookiePreference( FilterResponseEvent $event ): void {
		if ( !$event->isMasterRequest() ) {
			return;
		}

		$cookieConsent = $event->getRequest()->cookies->get('cookie_consent', 'unset' );
		$response = $event->getResponse();

		$response->setContent(str_replace(
			'data-application-vars=',
			'data-cookie-consent="' . $cookieConsent . '" data-application-vars=',
			$response->getContent()
		));
	}
}
