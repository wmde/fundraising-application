<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Validation\Rule;

use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\ValidationErrorLogger;

/**
 * @license GPL-2.0-or-later
 */
class MinBucketCountRule {

	public function validate( Campaign $campaign, ValidationErrorLogger $errorLogger ): bool {
		if ( count( $campaign->getBuckets() ) < 2 ) {
			$errorLogger->addError( 'Campaigns must have at least two buckets', $campaign );
			return false;
		}
		return true;
	}

}
