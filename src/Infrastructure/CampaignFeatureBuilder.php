<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use RemotelyLiving\Doorkeeper\Features\Feature;
use RemotelyLiving\Doorkeeper\Features\Set;
use RemotelyLiving\Doorkeeper\Rules\Percentage;
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
			$feature = new Feature( $this->getFeatureName( $campaign, $group ), true, $this->getRules( $campaign, $group ) );
			$featureSet->pushFeature( $feature );
		}
	}

	private function addDefaultCampaignFeatures( Campaign $campaign, Set $featureSet ) {
		foreach ( $campaign->getGroups() as $group ) {
			$feature = new Feature( $this->getFeatureName( $campaign, $group ), $group === $campaign->getDefaultGroup() );
			$featureSet->pushFeature( $feature );
		}
	}

	private function getFeatureName( Campaign $campaign, string $group ): string {
		return 'campaigns' . '.' . $campaign->getName() . '.' . $group;
	}

	private function getRules( Campaign $campaign, string $group ): array {
		if ( $group === $campaign->getDefaultGroup() ) {
			return [];
		}
		return [
			new TimeAfter( $campaign->getStartTimestamp()->format( 'Y-m-d H:i:s' ) ),
			new TimeBefore( $campaign->getEndTimestamp()->format( 'Y-m-d H:i:s' ) ),
			new Percentage( (int) round( 100 / count( $campaign->getGroups() ) ) )
		];
	}

}