<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use WMDE\Fundraising\Frontend\BucketTesting\FeatureToggle;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentInterval;

/**
 * Factory for generating classes whose implementations differ due to A/B testing.
 */
class ChoiceFactory {

	private FeatureToggle $featureToggle;

	public function __construct( FeatureToggle $featureToggle ) {
		$this->featureToggle = $featureToggle;
	}

	public function getMembershipPaymentIntervals(): array {
		if ( $this->featureToggle->featureIsActive( 'campaigns.membership_intervals.all_intervals' ) ) {
			return [
				PaymentInterval::Monthly->value,
				PaymentInterval::Quarterly->value,
				PaymentInterval::HalfYearly->value,
				PaymentInterval::Yearly->value,
			];
		} elseif ( $this->featureToggle->featureIsActive( 'campaigns.membership_intervals.some_intervals' ) ) {
			return [
				PaymentInterval::Monthly->value,
				PaymentInterval::Yearly->value,
			];
		}
		throw new UnknownChoiceDefinition( 'Failed to determine header template' );
	}
}
