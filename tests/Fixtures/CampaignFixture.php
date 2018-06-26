<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\BucketTesting\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Campaign;

/**
 * @license GNU GPL v2+
 */
class CampaignFixture {

	public static function createBucket(
		Campaign $campaign = null,
		string $bucketName = 'test',
		bool $isDefault = Bucket::DEFAULT
	): Bucket {
		if ($campaign === null) {
			$campaign = self::createCampaign();
		}
		$bucket = new Bucket( $bucketName, $campaign, $isDefault );
		$campaign->addBucket( $bucket );
		return $bucket;
	}

	public static function createCampaign(
		string $campaignName = 'test_campaign',
		string $campaignUrlKey = 'test_url_key',
		string $startTime = '2000-01-01',
		string $endTime = '2099-12-31',
		bool $isActive = Campaign::ACTIVE
	): Campaign {
		return new Campaign(
			$campaignName,
			$campaignUrlKey,
			new \DateTime( $startTime ),
			new \DateTime( $endTime ),
			$isActive
		);
	}
}
