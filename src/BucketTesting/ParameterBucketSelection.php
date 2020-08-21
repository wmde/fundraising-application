<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;

/**
 * @license GPL-2.0-or-later
 */
class ParameterBucketSelection implements BucketSelectionStrategy {

	private array $parameters;

	public function __construct( array $parameters ) {
		$this->parameters = $parameters;
	}

	public function selectBucketForCampaign( Campaign $campaign ): ?Bucket {
		$urlKey = $campaign->getUrlKey();
		if ( !isset( $this->parameters[$urlKey] ) ) {
			return null;
		}
		return $campaign->getBucketByIndex( $this->parameters[$urlKey] );
	}

}
