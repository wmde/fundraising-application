<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\BucketTesting;

class CampaignCollection {
	private $campaigns;

	public function __construct( Campaign ...$campaigns ) {
		$this->campaigns = $campaigns;
	}

	/**
	 * Use urlKey => BucketIndex values to select matching buckets from campaigns.
	 * Also return all campaigns that were not matched.
	 *
	 * @return array [ Bucket[], Campaign[] ]
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

	/**
	 * Select the most distant active campaign where the end date is not in the past
	 * @return null|Campaign
	 */
	public function getMostDistantCampaign(): ?Campaign {
		$now = new \DateTime();
		return array_reduce( $this->campaigns, function ( ?Campaign $mostDistant, Campaign $current ) use ( $now ) {
			if ( !$current->isActive() || $current->getEndTimestamp() < $now ) {
				return $mostDistant;
			}
			if ( $mostDistant === null ) {
				return $current;
			}
			return $mostDistant->getEndTimestamp() > $current->getEndTimestamp() ? $mostDistant : $current;
		}, null );
	}

}