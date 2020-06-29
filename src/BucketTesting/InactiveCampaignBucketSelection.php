<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

/**
 * @license GPL-2.0-or-later
 */
class InactiveCampaignBucketSelection implements BucketSelectionStrategy {

	private $now;

	public function __construct( CampaignDate $now ) {
		$this->now = $now;
	}

	public function selectBucketForCampaign( Campaign $campaign ): ?Bucket {
		if ( !$campaign->isActive() || $campaign->isExpired( $this->now ) ) {
			return $campaign->getDefaultBucket();
		}
		return null;
	}

}
