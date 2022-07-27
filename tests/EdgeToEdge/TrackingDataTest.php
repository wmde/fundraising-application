<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

/**
 * @covers \WMDE\Fundraising\Frontend\App\EventHandlers\RegisterTrackingData
 */
class TrackingDataTest extends WebRouteTestCase {

	private const PARAM_NAME_CAMPAIGN = 'piwik_campaign';
	private const PARAM_NAME_KEYWORD = 'piwik_kwd';

	public function testWhenTrackingParamsArePassed_trackingCodeIsAddedToRequest(): void {
		$this->markTestIncomplete( "This will work again when we have adapted the default controller" );
		$client = $this->createClient();
		$client->request( 'get', '/', [
			self::PARAM_NAME_CAMPAIGN => 'campaign',
			self::PARAM_NAME_KEYWORD => 'keyword'
		] );

		$this->assertEquals( 'campaign/keyword', $client->getRequest()->get( 'trackingCode' ) );
	}

}
