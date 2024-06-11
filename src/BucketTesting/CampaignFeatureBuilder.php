<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

use RemotelyLiving\Doorkeeper\Features\Feature;
use RemotelyLiving\Doorkeeper\Features\Set;
use RemotelyLiving\Doorkeeper\Rules\RuleInterface;
use RemotelyLiving\Doorkeeper\Rules\StringHash;
use RemotelyLiving\Doorkeeper\Rules\TimeAfter;
use RemotelyLiving\Doorkeeper\Rules\TimeBefore;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;

/**
 * Build a Doorkeeper feature set from a list of campaigns
 */
class CampaignFeatureBuilder {

	/** @var Campaign[] */
	private array $campaigns;

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

	private function addActiveCampaignFeatures( Campaign $campaign, Set $featureSet ): void {
		foreach ( $campaign->getBuckets() as $bucket ) {
			$feature = new Feature( $bucket->getId(), true, $this->getRules( $bucket ) );
			$featureSet->pushFeature( $feature );
		}
	}

	private function addDefaultCampaignFeatures( Campaign $campaign, Set $featureSet ): void {
		foreach ( $campaign->getBuckets() as $bucket ) {
			$feature = new Feature( $bucket->getId(), $bucket->isDefaultBucket() );
			$featureSet->pushFeature( $feature );
		}
	}

	/**
	 * @param Bucket $bucket
	 *
	 * @return StringHash[]
	 */
	private function getRules( Bucket $bucket ): array {
		$bucketNameMatch = new StringHash( $bucket->getId() );

		if ( !$bucket->isDefaultBucket() ) {
			$this->addDateRangePrerequisite( $bucketNameMatch, $bucket->getCampaign() );
		}

		return [ $bucketNameMatch ];
	}

	private function addDateRangePrerequisite( RuleInterface $rule, Campaign $campaign ): void {
		$dateRangeMatch = new TimeAfter( $campaign->getStartTimestamp()->format( 'Y-m-d H:i:s' ) );
		$dateRangeMatch->addPrerequisite( new TimeBefore( $campaign->getEndTimestamp()->format( 'Y-m-d H:i:s' ) ) );
		$rule->addPrerequisite( $dateRangeMatch );
	}

}
