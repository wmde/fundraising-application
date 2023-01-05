<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use WMDE\Fundraising\Frontend\BucketTesting\FeatureToggle;

/**
 * Factory for generating classes whose implementations differ due to A/B testing.
 *
 * @license GPL-2.0-or-later
 */
class ChoiceFactory {

	private FeatureToggle $featureToggle;

	public function __construct( FeatureToggle $featureToggle ) {
		$this->featureToggle = $featureToggle;
	}

	public function getMembershipPaymentIntervals(): array {
		if ( $this->featureToggle->featureIsActive( 'campaigns.membership_intervals.all_intervals' ) ) {
			return [ 1, 3, 6, 12 ];
		} elseif ( $this->featureToggle->featureIsActive( 'campaigns.membership_intervals.some_intervals' ) ) {
			return [ 1, 12 ];
		}
		throw new UnknownChoiceDefinition( 'Failed to determine header template' );
	}
}
