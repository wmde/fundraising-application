<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\BucketTesting;

class CampaignCollection {
	private $campaigns;

	public function __construct( Campaign ...$campaigns ) {
		$this->campaigns = $campaigns;
	}

	/**
	 * Differentiate between buckets of campaigns for which the user is already part of
	 * and the campaigns for which the user is not part of yet
	 */
	public function splitBucketsFromCampaigns( array $params ): array {
		$buckets = [];
		$campaigns = [];
		foreach ( $this->campaigns as $campaign ) {
			$urlKey = $campaign->getUrlKey();
			if ( isset( $params[$urlKey] ) && $bucket = $campaign->getBucketByIndex( $params[$urlKey] ) ) {
				$buckets[]= $bucket;
				continue;
			}
			$campaigns[]= $campaign;
		}
		return [ $buckets, $campaigns ];
	}

}