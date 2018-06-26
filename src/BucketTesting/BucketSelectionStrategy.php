<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

interface BucketSelectionStrategy {
	public function selectBucketForCampaign( Campaign $campaign ): ?Bucket;
}