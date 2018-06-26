<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

use LogicException;

class BucketSelector {

	private $campaigns;
	private $fallbackSelectionStrategy;

	public function __construct( CampaignCollection $campaigns, BucketSelectionStrategy $fallbackSelectionStrategy ) {
		$this->campaigns = $campaigns;
		$this->fallbackSelectionStrategy = $fallbackSelectionStrategy;
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
		$possibleParameters = array_merge( $cookie, $urlParameters );

		$selectionStrategies = [
			new InactiveCampaignBucketSelection(),
			new ParameterBucketSelection( $possibleParameters ),
			$this->fallbackSelectionStrategy
		];

		$buckets = [];
		foreach( $this->campaigns as $campaign ) {
			$buckets[] = $this->selectBucketWithStrategies( $selectionStrategies, $campaign );
		}
		return $buckets;
	}

	private function selectBucketWithStrategies( array $selectionStrategies, Campaign $campaign ): Bucket {
		foreach( $selectionStrategies as $strategy ) {
			if ( $bucket = $strategy->selectBucketForCampaign( $campaign ) ) {
				return $bucket;
			}
		}
		throw new LogicException( 'Fallback bucket selection class must always return a bucket.' );
	}

}