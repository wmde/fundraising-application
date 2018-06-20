<?php

namespace WMDE\Fundraising\Frontend\Infrastructure\BucketTesting;

class CampaignCollection {
	private $campaigns;

	public function __construct( Campaign ...$campaigns ) {
		$this->campaigns = $campaigns;
	}

	public function splitBucketsFromCampaigns( array $params ): array {
		$buckets = [];
		$campaigns = [];
		foreach ( $this->campaigns as $campaign ) {
			$urlKey = $campaign->getUrlKey();
			if ( isset( $params[ $urlKey ] ) && $bucket = $campaign->getBucketByIndex( $params[ $urlKey ]  ) ) {
				$buckets []= $bucket;
				continue;
			}
			$campaigns []= $campaign;
		}
		return [ $buckets, $campaigns ];
	}

}