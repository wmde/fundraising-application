<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\Fixtures\OverridingCampaignConfigurationLoader;

/**
 * @covers \WMDE\Fundraising\Frontend\App\EventHandlers\StoreBucketSelection
 */
class CampaignCookieTest extends WebRouteTestCase {

	private const TEST_CAMPAIGN_CONFIG = [
		'awesome_feature' => [
			'url_key' => 'omg',
			'start' => '2018-01-01',
			'end' => '2099-12-31',
			'active' => true,
			'buckets' => [ 'boring_default', 'awesome_test' ],
			'default_bucket' => 'boring_default'
		]
	];

	public function testWhenUserVisitsWithBucketParams_bucketsAreSet(): void {
		$this->modifyConfiguration( [ 'campaigns' => [ 'timezone' => 'UTC' ] ] );
		$this->modifyEnvironment( static function ( FunFunFactory $ffactory ) {
			$ffactory->setCampaignConfigurationLoader(
				new OverridingCampaignConfigurationLoader(
					$ffactory->getCampaignConfigurationLoader(),
					self::TEST_CAMPAIGN_CONFIG
				)
			);
		} );

		$client = $this->createClient();
		$client->request( 'get', '/', [ 'omg' => 1 ] );

		$buckets = array_filter(
			$this->getFactory()->getSelectedBuckets(),
			fn ( Bucket $bucket ) => $bucket->getCampaign()->getName() === 'awesome_feature'
		);
		$buckets = array_values( $buckets );

		$this->assertCount( 1, $buckets );
		$this->assertEquals( 'awesome_test', $buckets[0]->getName() );
	}
}
