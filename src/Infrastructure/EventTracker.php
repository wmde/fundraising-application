<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

class EventTracker {

	private ServerSideTracker $tracker;

	public function __construct( ServerSideTracker $tracker ) {
		$this->tracker = $tracker;
	}

	public function trackEvent( string $category, string $action, string $eventData ) {
		$this->tracker->trackEvent( $category, $action, $eventData );
	}
}
