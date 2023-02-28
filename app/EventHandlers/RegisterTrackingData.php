<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\EventHandlers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use WMDE\Fundraising\Frontend\Infrastructure\TrackingDataSelector;

/**
 * Inject the request object with the current tracking data from URL params.
 *
 * In previous iterations of this class, the data could also come from a cookie, with URL params taking precedence,
 * but we dropped the cookie storage for GDPR reasons.
 */
class RegisterTrackingData implements EventSubscriberInterface {

	/**
	 * @return array<string, string>
	 */
	public static function getSubscribedEvents(): array {
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
