<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\EventHandlers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use WMDE\Fundraising\Frontend\Infrastructure\TrackingDataSelector;

/**
 * Inject the request object with the current tracking data, stored in cookie or coming from URL params.
 *
 * Cookie values take precedence over
 */
class RegisterTrackingData implements EventSubscriberInterface {

	public static function getSubscribedEvents() {
		return [
			KernelEvents::REQUEST => 'onKernelRequest',
		];
	}

	public function onKernelRequest( RequestEvent $event ): void {
		$request = $event->getRequest();

		$request->attributes->set( 'trackingCode', TrackingDataSelector::concatTrackingFromVarTuple(
			$request->get( 'piwik_campaign', '' ),
			$request->get( 'piwik_kwd', '' )
		) );
	}

}
