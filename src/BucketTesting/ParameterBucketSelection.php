<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;

class ParameterBucketSelection implements BucketSelectionStrategy {

	/**
	 * @param array<string, scalar> $parameters
	 */
	public function __construct( private readonly array $parameters ) {
	}

	public function selectBucketForCampaign( Campaign $campaign ): ?Bucket {
		$urlKey = $campaign->getUrlKey();
		if ( !isset( $this->parameters[$urlKey] ) ) {
			return null;
		}
		return $campaign->getBucketByIndex( $this->parameters[$urlKey] );
	}

}
