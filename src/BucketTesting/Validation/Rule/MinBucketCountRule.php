<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Validation\Rule;

use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\CampaignErrorCollection;

class MinBucketCountRule implements CampaignValidationRuleInterface {

	public function validate( Campaign $campaign, CampaignErrorCollection $errorLogger ): bool {
		if ( count( $campaign->getBuckets() ) < 2 ) {
			$errorLogger->addError( 'Campaigns must have at least two buckets', $campaign );
			return false;
		}
		return true;
	}

}
