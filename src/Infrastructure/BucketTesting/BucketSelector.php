<?php

namespace WMDE\Fundraising\Frontend\Infrastructure\BucketTesting;

class BucketSelector {

	private $campaigns;
 	private $cookie;
 	private $urlParameters;

	public function __construct( CampaignCollection $campaigns, array $cookie = [], array $urlParameters = [] ) {
		$this->campaigns = $campaigns;
		$this->cookie = $cookie;
		$this->urlParameters = $urlParameters;
	}

	public function setCookie( array $cookie ): self {
		$this->cookie = $this->sanitizeParameters( $cookie );
		return $this;
	}

	public function setUrlParameters( array $urlParameters ): self {
		$this->urlParameters = $this->sanitizeParameters( $urlParameters );
		return $this;
	}

	private function sanitizeParameters( array $params ): array {
		$sanitized = [];
		foreach ( $params as $key => $value ) {
			if ( ctype_digit( $value ) ) {
				$sanitized[$key] = intval( $value );
			}
		}
		return $sanitized;
	}

	/**
	 * @return Bucket[]
	 */
	public function selectBuckets(): array {
		$possibleKeys = array_merge( $this->cookie, $this->urlParameters );
		[ $buckets, $missingCampaigns ] = $this->campaigns->splitBucketsFromCampaigns( $possibleKeys );

		foreach($missingCampaigns as $campaign) {
				$buckets []= $campaign->getBuckets()[ array_rand( $campaign->getBuckets() ) ];

		}
		return $buckets;
	}

}