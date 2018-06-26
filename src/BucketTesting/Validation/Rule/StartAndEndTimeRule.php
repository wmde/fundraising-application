<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Validation\Rule;

use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\ValidationErrorLogger;

/**
 * @license GNU GPL v2+
 */
class StartAndEndTimeRule {

	public function validate( Campaign $campaign, ValidationErrorLogger $errorLogger ): bool {
		if ( $campaign->getStartTimestamp() >= $campaign->getEndTimestamp() ) {
			$errorLogger->addError( 'Start date must be before end date.', $campaign );
			return false;
		}
		return true;
	}

}