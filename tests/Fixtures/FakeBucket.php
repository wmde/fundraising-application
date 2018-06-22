<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\BucketTesting\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Campaign;

/**
 * @license GNU GPL v2+
 */
class FakeBucket {

	public static function createBucket(
		string $bucketName = 'test',
		bool $isDefault = true,
		string $campaignName = 'test_campaign',
		string $campaignUrlKey = 'test_url_key'
	): Bucket {
		$campaign = new Campaign(
			$campaignName,
			$campaignUrlKey,
			new \DateTime( '2000-01-01' ),
			new \DateTime( '2099-12-31' ),
			true
		);
		$bucket = new Bucket( $bucketName, $campaign, $isDefault );
		$campaign->addBucket( $bucket );
		return $bucket;
	}
}
