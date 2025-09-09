<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use PHPUnit\Framework\Attributes\CoversClass;
use WMDE\Fundraising\Frontend\App\EventHandlers\StoreBucketSelection;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\Tests\Fixtures\OverridingCampaignConfigurationLoader;

#[CoversClass( StoreBucketSelection::class )]
class CampaignBucketTest extends WebRouteTestCase {

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
		$this->modifyConfiguration( [
			'campaigns' => [ 'timezone' => 'UTC' ],
			'skin' => 'laika'
		] );
		$client = $this->createClient();
		$factory = $this->getFactory();
		$factory->setCampaignConfigurationLoader(
			new OverridingCampaignConfigurationLoader(
				$factory->getCampaignConfigurationLoader(),
				self::TEST_CAMPAIGN_CONFIG
			)
		);

		$client->request( 'get', '/', [ 'omg' => 1 ] );

		$buckets = array_filter(
			$this->getFactory()->getSelectedBuckets(),
			static fn ( Bucket $bucket ) => $bucket->getCampaign()->getName() === 'awesome_feature'
		);
		$buckets = array_values( $buckets );

		$this->assertCount( 1, $buckets );
		$this->assertEquals( 'awesome_test', $buckets[0]->getName() );
	}
}
