<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\EventHandlers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use WMDE\Fundraising\Frontend\App\CookieNames;
use WMDE\Fundraising\Frontend\Infrastructure\CookieBuilder;
use WMDE\Fundraising\Frontend\Infrastructure\TrackingDataSelector;

/**
 * Inject the request object with the current tracking data, stored in cookie or coming from URL params.
 *
 * Cookie values take precedence over
 */
class RegisterTrackingData implements EventSubscriberInterface {

	public const SHOULD_STORE_TRACKING_COOKIE = 'shouldStoreTrackingCookie';

	public static function getSubscribedEvents() {
		return [
			KernelEvents::REQUEST => 'onKernelRequest',
			KernelEvents::RESPONSE => 'onKernelResponse'
		];
	}

	private CookieBuilder $cookieBuilder;

	public function __construct( CookieBuilder $cookieBuilder ) {
		$this->cookieBuilder = $cookieBuilder;
	}

	public function onKernelRequest( RequestEvent $event ): void {
		$request = $event->getRequest();
		$shouldStoreTracking = $request->cookies->get( CookieNames::CONSENT ) === 'yes';
		$request->attributes->set( self::SHOULD_STORE_TRACKING_COOKIE, $shouldStoreTracking );

		if ( !$shouldStoreTracking ) {
			return;
		}

		$request->attributes->set( 'trackingCode', TrackingDataSelector::getFirstNonEmptyValue( [
			$request->cookies->get( CookieNames::TRACKING ),
			TrackingDataSelector::concatTrackingFromVarTuple(
				$request->get( 'piwik_campaign', '' ),
				$request->get( 'piwik_kwd', '' )
			)
		] ) );
	}

	public function onKernelResponse( ResponseEvent $event ): void {
		$request = $event->getRequest();
		$shouldStoreCookie = $request->attributes->get( self::SHOULD_STORE_TRACKING_COOKIE );
		$trackingCode = $request->attributes->get( 'trackingCode', '' );

		if ( !$shouldStoreCookie || $trackingCode === '' ) {
			return;
		}

		$event->getResponse()->headers->setCookie( $this->cookieBuilder->newCookie(
			CookieNames::TRACKING,
			$trackingCode
		) );
	}

}
