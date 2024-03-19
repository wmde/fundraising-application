<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\CampaignDate;

class InactiveCampaignBucketSelection implements BucketSelectionStrategy {

	public function __construct( private readonly CampaignDate $now ) {
	}

	public function selectBucketForCampaign( Campaign $campaign ): ?Bucket {
		if ( !$campaign->isActive() || $campaign->isExpired( $this->now ) ) {
			return $campaign->getDefaultBucket();
		}
		return null;
	}

}
