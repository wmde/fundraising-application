<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * @license GNU GPL v2+
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

	public function setIp( string $ip ): void {
		$this->tracker->setIp( $ip );
	}

}