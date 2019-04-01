<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use WMDE\Fundraising\Frontend\Tests\Fixtures\ServerSideTrackerSpy;
use WMDE\Fundraising\Frontend\Infrastructure\PageViewTracker;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\PageViewTracker
 *
 * @licence GNU GPL v2+
 * @author Leszek Manicki <leszek.manicki@wikimedia.de>
 */
class PageViewTrackerTest extends \PHPUnit\Framework\TestCase {

	public function testTrackPaypalRedirection(): void {
		$tracker = new ServerSideTrackerSpy();
		$pageViewTracker = new PageViewTracker( $tracker, 'http://awesome.url' );

		$pageViewTracker->trackPaypalRedirection( 'foo-campaign', 'foo-keyword', '10.1.2.3' );

		$trackedPageViews = $tracker->getPageViews();
		$this->assertCount( 1, $trackedPageViews );

		$this->assertSame(
			'http://awesome.url/paypal-redir/?piwik_campaign=foo-campaign&piwik_kwd=foo-keyword',
			$trackedPageViews[0]['url']
		);

		$this->assertSame(
			'Redirection from mobile banner to PayPal',
			$trackedPageViews[0]['title']
		);

		$this->assertSame( '10.1.2.3', $tracker->getCallsToSetIp()[0], 'IP address must be set' );
	}

}
