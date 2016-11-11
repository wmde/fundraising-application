<?php

namespace WMDE\Fundraising\Tests\Integration;

use WMDE\Fundraising\Frontend\Tests\Fixtures\ServerSideTrackerSpy;
use WMDE\Fundraising\Frontend\Infrastructure\PageViewTracker;

/**
 * @covers WMDE\Fundraising\Frontend\Infrastructure\PageViewTracker
 *
 * @licence GNU GPL v2+
 * @author Leszek Manicki <leszek.manicki@wikimedia.de>
 */
class PageViewTrackerTest extends \PHPUnit_Framework_TestCase {

	public function testTrackPaypalRedirection() {
		$tracker = new ServerSideTrackerSpy();
		$pageViewTracker = new PageViewTracker( $tracker, 'http://awesome.url' );

		$pageViewTracker->trackPaypalRedirection( 'foo-campaign', 'foo-keyword' );

		$trackedPageViews = $tracker->getPageViews();
		$this->assertCount( 1, $trackedPageViews );
		$this->assertEquals(
			'http://awesome.url/paypal-redir/?piwik_campaign=foo-campaign&piwik_kwd=foo-keyword',
			$trackedPageViews[0]['url']
		);
		$this->assertEquals( 'Redirection from mobile banner to PayPal', $trackedPageViews[0]['title'] );
	}

}
