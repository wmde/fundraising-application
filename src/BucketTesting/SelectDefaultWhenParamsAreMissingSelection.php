<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;

class SelectDefaultWhenParamsAreMissingSelection implements BucketSelectionStrategy {

	private array $params;

	public function __construct( array $params ) {
		$this->params = $params;
	}

	public function selectBucketForCampaign( Campaign $campaign ): ?Bucket {
		if ( $campaign->isOnlyActiveWithUrlKey() && !isset( $this->params[$campaign->getUrlKey()] ) ) {
			return $campaign->getDefaultBucket();
		}
		return null;
	}
}
