<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

/**
 * @license GNU GPL v2+
 */
class ParameterBucketSelection implements BucketSelectionStrategy {

	private $parameters;

	public function __construct( array $parameters ) {
		$this->parameters = $parameters;
	}

	public function selectBucketForCampaign( Campaign $campaign ): ?Bucket {
		$urlKey = $campaign->getUrlKey();
		if ( isset( $this->parameters[$urlKey] ) &&
				$bucket = $campaign->getBucketByIndex( $this->parameters[$urlKey] ) ) {
			return $bucket;
		}
		return null;
	}

}