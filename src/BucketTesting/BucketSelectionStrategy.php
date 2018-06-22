<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

interface BucketSelectionStrategy {
	public function selectBucketFromCampaign( Campaign $campaign ): Bucket;
}