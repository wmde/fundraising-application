<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Infrastructure\ServerSideTracker;

/**
 * @license GPL-2.0-or-later
 * @author Leszek Manicki <leszek.manicki@wikimedia.de>
 */
class ServerSideTrackerSpy implements ServerSideTracker {

	private $pageViews = [];
	private $events = [];

	private $ips = [];

	public function trackPageView( string $url, string $title ): void {
		$this->pageViews[] = [
			'url' => $url,
			'title' => $title,
		];
	}

	public function getPageViews(): array {
		return $this->pageViews;
	}

	public function setIp( string $ip ): void {
		$this->ips[] = $ip;
	}

	public function getCallsToSetIp(): array {
		return $this->ips;
	}

	public function trackEvent( string $category, string $action, string $eventData ): void {
		$this->events[] = [
			'category' => $category,
			'action' => $action,
			'eventData' => $eventData,
		];
	}
}
