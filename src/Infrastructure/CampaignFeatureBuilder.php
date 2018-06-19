<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

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
		foreach ( $campaign->getGroups() as $group ) {
			$feature = new Feature( $this->getFeatureName( $group ), true, $this->getRules( $group ) );
			$featureSet->pushFeature( $feature );
		}
	}

	private function addDefaultCampaignFeatures( Campaign $campaign, Set $featureSet ) {
		foreach ( $campaign->getGroups() as $group ) {
			$feature = new Feature( $this->getFeatureName( $group ), $group->isDefaultGroup() );
			$featureSet->pushFeature( $feature );
		}
	}

	private function getFeatureName( Group $group ): string {
		return 'campaigns' . '.' . $group->getCampaign()->getName() . '.' . $group->getName();
	}

	private function getRules( Group $group ): array {
		if ( $group->isDefaultGroup() ) {
			return [];
		}
		$campaign = $group->getCampaign();
		$dateRangeMatch = new TimeAfter( $campaign->getStartTimestamp()->format( 'Y-m-d H:i:s' ) );
		$dateRangeMatch->addPrerequisite( new TimeBefore( $campaign->getEndTimestamp()->format( 'Y-m-d H:i:s' ) ) );
		$groupNameMatch = new StringHash( $group->getName()  );
		$groupNameMatch->addPrerequisite( $dateRangeMatch );

		return [
			$groupNameMatch
		];
	}

}