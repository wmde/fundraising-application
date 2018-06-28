<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Validation\Rule;

use WMDE\Fundraising\Frontend\BucketTesting\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\ValidationErrorLogger;

/**
 * @license GNU GPL v2+
 */
class UniqueBucketRule {

	public function validate( Campaign $campaign, ValidationErrorLogger $errorLogger ): bool {
		$buckets = [];
		$valid = true;
		foreach ( $campaign->getBuckets() as $bucket ) {
			if ( in_array( $bucket->getName(), $buckets ) ) {
				$valid = false;
				$errorLogger->addError( 'Duplicate bucket ' . $bucket->getName(), $campaign );
			}
			$buckets[] = $bucket->getName();
		}
		return $valid;
	}

}