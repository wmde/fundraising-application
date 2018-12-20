<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignBuilder;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignDate;

/**
 * @covers \WMDE\Fundraising\Frontend\BucketTesting\CampaignBuilder
 */
class CampaignBuilderTest extends TestCase {

	public function testCampaignsAreBuiltFromConfiguration() {
		$firstExpectedCampaign = new Campaign(
			'first',
			'f',
			new CampaignDate( '2018-10-10', new \DateTimeZone( 'UTC' ) ),
			new CampaignDate( '2018-12-12', new \DateTimeZone( 'UTC' ) ),
			true
		);
		$firstExpectedCampaign
			->addBucket( new Bucket( 'bucket1', $firstExpectedCampaign, Bucket::DEFAULT ) )
			->addBucket( new Bucket( 'bucket2', $firstExpectedCampaign, Bucket::NON_DEFAULT ) );
		$secondExpectedCampaign = new Campaign(
			'second',
			's',
			new CampaignDate( '2019-01-01', new \DateTimeZone( 'UTC' ) ),
			new CampaignDate( '2025-12-31', new \DateTimeZone( 'UTC' ) ),
			false
		);
		$secondExpectedCampaign
			->addBucket( new Bucket( 'example1', $secondExpectedCampaign, Bucket::NON_DEFAULT ) )
			->addBucket( new Bucket( 'example2', $secondExpectedCampaign, Bucket::NON_DEFAULT ) )
			->addBucket( new Bucket( 'default', $secondExpectedCampaign, Bucket::DEFAULT ) );

		$builder = new CampaignBuilder( new \DateTimeZone( 'UTC' ) );
		$campaigns = $builder->getCampaigns(
			[
				'first' => [
					'start' => '2018-10-10',
					'end' => '2018-12-12',
					'active' => true,
					'buckets' => [ 'bucket1', 'bucket2' ],
					'default_bucket' => 'bucket1',
					'url_key' => 'f'
				],
				'second' => [
					'start' => '2019-01-01',
					'end' => '2025-12-31',
					'active' => false,
					'buckets' => [ 'example1', 'example2', 'default' ],
					'default_bucket' => 'default',
					'url_key' => 's'
				],
			]
		);

		$this->assertEquals( [ $firstExpectedCampaign, $secondExpectedCampaign ], $campaigns );
	}

	public function testTimeRangeIsConvertedToUtcFromTimezone() {
		$firstExpectedCampaign = new Campaign(
			'first',
			'f',
			new CampaignDate( '2018-10-10 2:00:00', new \DateTimeZone( 'UTC' ) ),
			new CampaignDate( '2018-12-12 2:00:00', new \DateTimeZone( 'UTC' ) ),
			true
		);
		$firstExpectedCampaign
			->addBucket( new Bucket( 'bucket1', $firstExpectedCampaign, Bucket::DEFAULT ) )
			->addBucket( new Bucket( 'bucket2', $firstExpectedCampaign, Bucket::NON_DEFAULT ) );
		$secondExpectedCampaign = new Campaign(
			'second',
			's',
			new CampaignDate( '2019-01-01 2:00:00', new \DateTimeZone( 'UTC' ) ),
			new CampaignDate( '2025-12-31 2:00:00', new \DateTimeZone( 'UTC' ) ),
			false
		);
		$secondExpectedCampaign
			->addBucket( new Bucket( 'example1', $secondExpectedCampaign, Bucket::NON_DEFAULT ) )
			->addBucket( new Bucket( 'example2', $secondExpectedCampaign, Bucket::NON_DEFAULT ) )
			->addBucket( new Bucket( 'default', $secondExpectedCampaign, Bucket::DEFAULT ) );

		$builder = new CampaignBuilder( new \DateTimeZone( '-200' ) );

		$campaigns = $builder->getCampaigns(
			[
				'first' => [
					'start' => '2018-10-10',
					'end' => '2018-12-12',
					'active' => true,
					'buckets' => [ 'bucket1', 'bucket2' ],
					'default_bucket' => 'bucket1',
					'url_key' => 'f'
				],
				'second' => [
					'start' => '2019-01-01',
					'end' => '2025-12-31',
					'active' => false,
					'buckets' => [ 'example1', 'example2', 'default' ],
					'default_bucket' => 'default',
					'url_key' => 's'
				],
			]
		);

		$this->assertEquals( [ $firstExpectedCampaign, $secondExpectedCampaign ], $campaigns );
	}
}
