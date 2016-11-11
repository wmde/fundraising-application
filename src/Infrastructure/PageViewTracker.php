<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * @licence GNU GPL v2+
 * @author Leszek Manicki <leszek.manicki@wikimedia.de>
 */
class PageViewTracker {

	const TRACKING_TITLE_PAYPAL_REDIRECT = 'Redirection from mobile banner to PayPal';

	private $tracker;

	private $trackingUrlBase;

	public function __construct( ServerSideTracker $tracker, string $trackingUrlBase ) {
		$this->tracker = $tracker;
		$this->trackingUrlBase = $trackingUrlBase;
	}

	public function trackPaypalRedirection( string $campaign, string $keyword ) {
		$trackingUrl = $this->getPaypalRedirectionTrackingUrl( $campaign, $keyword );
		$this->trackPageView( $trackingUrl, self::TRACKING_TITLE_PAYPAL_REDIRECT );
	}

	private function getPaypalRedirectionTrackingUrl( string $campaign, string $keyword ): string {
		return $this->trackingUrlBase . '/paypal-redir/' .
		'?piwik_campaign=' . urlencode( $campaign ) .
		'&piwik_kwd=' . urlencode( $keyword );
	}

	private function trackPageView( string $trackingUrl, string $title ) {
		$this->tracker->trackPageView( $trackingUrl, $title );
	}

}
