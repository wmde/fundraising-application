<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * @license GPL-2.0-or-later
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class PiwikServerSideTracker implements ServerSideTracker {

	private $tracker;

	public function __construct( \PiwikTracker $tracker ) {
		$this->tracker = $tracker;
	}

	public function trackPageView( string $url, string $title ): void {
		$this->tracker->setUrl( $url );
		$this->tracker->doTrackPageView( $title );
	}

	public function trackEvent( string $category, string $action, string $eventData ): void {
		$this->tracker->doTrackEvent( $category, $action, $eventData );
	}

	public function setIp( string $ip ): void {
		$this->tracker->setIp( $ip );
	}

}
