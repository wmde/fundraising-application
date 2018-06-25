<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

use DateTime;

/**
 * @license GNU GPL v2+
 */
class InactiveCampaignBucketSelection implements BucketSelectionStrategy {

	private $now;

	public function __construct( ?DateTime $now = null ) {
		$this->now = $now ?: new DateTime();
	}

	public function selectBucketForCampaign( Campaign $campaign ): ?Bucket {
		if ( !$campaign->isActive() || $campaign->isExpired( $this->now ) ) {
			return $campaign->getDefaultBucket();
		}
		return null;
	}


}