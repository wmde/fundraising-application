<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Validation\Rule;

use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\CampaignErrorCollection;

/**
 * @license GPL-2.0-or-later
 */
class UniqueBucketRule implements CampaignValidationRuleInterface {

	public function validate( Campaign $campaign, CampaignErrorCollection $errorLogger ): bool {
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
