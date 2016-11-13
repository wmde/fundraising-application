<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Infrastructure\ServerSideTracker;

/**
 * @licence GNU GPL v2+
 * @author Leszek Manicki <leszek.manicki@wikimedia.de>
 */
class ServerSideTrackerSpy implements ServerSideTracker {

	/**
	 * @var string[]
	 */
	private $pageViews = [];

	private $ips = [];

	public function trackPageView( string $url, string $title ) {
		$this->pageViews[] = [
			'url' => $url,
			'title' => $title,
		];
	}

	/**
	 * @return string[]
	 */
	public function getPageViews(): array {
		return $this->pageViews;
	}

	public function setIp( string $ip ) {
		$this->ips[] = $ip;
	}

	public function getIps(): array {
		return $this->ips;
	}

}
