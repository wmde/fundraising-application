<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

class BucketSelector {

	private $campaigns;
	private $selectionStrategy;

	public function __construct( CampaignCollection $campaigns, BucketSelectionStrategy $selectionStrategy ) {
		$this->campaigns = $campaigns;
		$this->selectionStrategy = $selectionStrategy;
	}

	private function sanitizeParameters( array $params ): array {
		$sanitized = [];
		foreach ( $params as $key => $value ) {
			if ( is_int( $value ) || ctype_digit( $value ) ) {
				$sanitized[$key] = intval( $value );
			}
		}
		return $sanitized;
	}

	public function selectBuckets( array $cookie = [], array $urlParameters = [] ): array {
		$urlParameters = $this->sanitizeParameters( $urlParameters );
		$cookie = $this->sanitizeParameters( $cookie );
		$possibleKeys = array_merge( $cookie, $urlParameters );

		[ $buckets, $missingCampaigns ] = $this->campaigns->splitBucketsFromCampaigns( $possibleKeys );

		foreach( $missingCampaigns as $campaign ) {
			$buckets[] = $this->selectionStrategy->selectBucketFromCampaign( $campaign );
		}
		return $buckets;
	}

}