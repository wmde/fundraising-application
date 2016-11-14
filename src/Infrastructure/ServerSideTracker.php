<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * Server-Side triggering of page view tracking
 *
 * @licence GNU GPL v2+
 * @author Leszek Manicki <leszek.manicki@wikimedia.de>
 */
interface ServerSideTracker {

	public function trackPageView( string $url, string $title );

	public function setIp( string $ip );

}
