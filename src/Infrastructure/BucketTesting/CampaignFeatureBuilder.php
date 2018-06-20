<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\BucketTesting;

use RemotelyLiving\Doorkeeper\Features\Feature;
use RemotelyLiving\Doorkeeper\Features\Set;
use RemotelyLiving\Doorkeeper\Rules\Percentage;
use RemotelyLiving\Doorkeeper\Rules\StringHash;
use RemotelyLiving\Doorkeeper\Rules\TimeAfter;
use RemotelyLiving\Doorkeeper\Rules\TimeBefore;

/**
 * Build a Doorkeeper feature set from a list of campaigns
 *
 * @license GNU GPL v2+
 */
class CampaignFeatureBuilder {

	private $campaigns;

	public function __construct( Campaign ...$campaigns ) {
		$this->campaigns = $campaigns;
	}

	public function getFeatures(): Set {
		$featureSet = new Set();
		foreach ( $this->campaigns as $campaign ) {
			if ( $campaign->isActive() ) {
				$this->addActiveCampaignFeatures( $campaign, $featureSet );
				continue;
			}
			$this->addDefaultCampaignFeatures( $campaign, $featureSet );
		}
		return $featureSet;
	}

	private function addActiveCampaignFeatures( Campaign $campaign, Set $featureSet ) {
		foreach ( $campaign->getBuckets() as $bucket ) {
			$feature = new Feature( $this->getFeatureName( $bucket ), true, $this->getRules( $bucket ) );
			$featureSet->pushFeature( $feature );
		}
	}

	private function addDefaultCampaignFeatures( Campaign $campaign, Set $featureSet ) {
		foreach ( $campaign->getBuckets() as $bucket ) {
			$feature = new Feature( $this->getFeatureName( $bucket ), $bucket->isDefaultBucket() );
			$featureSet->pushFeature( $feature );
		}
	}

	private function getFeatureName( Bucket $bucket ): string {
		return 'campaigns' . '.' . $bucket->getCampaign()->getName() . '.' . $bucket->getName();
	}

	private function getRules( Bucket $bucket ): array {
		if ( $bucket->isDefaultBucket() ) {
			return [];
		}
		$campaign = $bucket->getCampaign();
		$dateRangeMatch = new TimeAfter( $campaign->getStartTimestamp()->format( 'Y-m-d H:i:s' ) );
		$dateRangeMatch->addPrerequisite( new TimeBefore( $campaign->getEndTimestamp()->format( 'Y-m-d H:i:s' ) ) );
		$bucketNameMatch = new StringHash( $bucket->getId()  );
		$bucketNameMatch->addPrerequisite( $dateRangeMatch );

		return [
			$bucketNameMatch
		];
	}

}