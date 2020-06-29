<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

/**
 * @license GPL-2.0-or-later
 */
class SelectDefaultWhenParamsAreMissingSelection implements BucketSelectionStrategy {

	private $params;

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
