<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

/**
 * @license GNU GPL v2+
 */
class RandomBucketSelection implements BucketSelectionStrategy {
	public function selectBucketForCampaign( Campaign $campaign ): Bucket {
		return $campaign->getBuckets()[array_rand( $campaign->getBuckets() )];
	}

}