<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

use LogicException;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\CampaignDate;

class BucketSelector {

	private CampaignCollection $campaigns;
	private BucketSelectionStrategy $fallbackSelectionStrategy;

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

	/**
	 * @param array $urlParameters
	 * @return Bucket[]
	 */
	public function selectBuckets( array $urlParameters = [] ): array {
		$possibleParameters = $this->sanitizeParameters( $urlParameters );

		$selectionStrategies = [
			new InactiveCampaignBucketSelection( new CampaignDate() ),
			new ParameterBucketSelection( $possibleParameters ),
			new SelectDefaultWhenParamsAreMissingSelection( $possibleParameters ),
			$this->fallbackSelectionStrategy
		];

		$buckets = [];
		foreach ( $this->campaigns as $campaign ) {
			$buckets[] = $this->selectBucketWithStrategies( $selectionStrategies, $campaign );
		}
		return $buckets;
	}

	private function selectBucketWithStrategies( array $selectionStrategies, Campaign $campaign ): Bucket {
		foreach ( $selectionStrategies as $strategy ) {
			$bucket = $strategy->selectBucketForCampaign( $campaign );
			if ( $bucket ) {
				return $bucket;
			}
		}
		throw new LogicException( 'Fallback bucket selection class must always return a bucket.' );
	}
}
