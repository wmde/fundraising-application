<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

class BucketSelector {

	private $campaigns;

	public function __construct( CampaignCollection $campaigns ) {
		$this->campaigns = $campaigns;
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	private function sanitizeParameters( array $params ): array {
		$sanitized = [];
		foreach ( $params as $key => $value ) {
			if ( is_int( $value ) || ctype_digit( $value ) ) {
				$sanitized[$key] = intval( $value );
			}
		}
		return $sanitized;
	}

	/**
	 * @param array $cookie
	 * @param array $urlParameters
	 *
	 * @return array
	 */
	public function selectBuckets( array $cookie = [], array $urlParameters = [] ): array {
		$urlParameters = $this->sanitizeParameters( $urlParameters );
		$cookie = $this->sanitizeParameters( $cookie );
		$possibleKeys = array_merge( $cookie, $urlParameters );
		[ $buckets, $missingCampaigns ] = $this->campaigns->splitBucketsFromCampaigns( $possibleKeys );

		foreach( $missingCampaigns as $campaign ) {
			$buckets[] = $campaign->getBuckets()[ array_rand( $campaign->getBuckets() ) ];
		}
		return $buckets;
	}

}