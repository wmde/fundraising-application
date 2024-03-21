<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;

class RandomBucketSelection implements BucketSelectionStrategy {
	public function selectBucketForCampaign( Campaign $campaign ): Bucket {
		return $campaign->getBuckets()[array_rand( $campaign->getBuckets() )];
	}

}
