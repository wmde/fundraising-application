<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * Server-Side triggering of page view tracking
 *
 * @license GPL-2.0-or-later
 * @author Leszek Manicki <leszek.manicki@wikimedia.de>
 */
interface ServerSideTracker {

	public function trackPageView( string $url, string $title ): void;

	public function trackEvent( string $category, string $action, string $eventData ): void;

	public function setIp( string $ip ): void;

}
