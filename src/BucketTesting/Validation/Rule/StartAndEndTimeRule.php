<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Validation\Rule;

use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\CampaignErrorCollection;

class StartAndEndTimeRule implements CampaignValidationRuleInterface {

	public function validate( Campaign $campaign, CampaignErrorCollection $errorLogger ): bool {
		if ( $campaign->getStartTimestamp() >= $campaign->getEndTimestamp() ) {
			$errorLogger->addError( 'Start date must be before end date.', $campaign );
			return false;
		}
		return true;
	}

}
