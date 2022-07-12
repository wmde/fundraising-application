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

	private $featureToggle;

	public function __construct( FeatureToggle $featureToggle ) {
		$this->featureToggle = $featureToggle;
	}

	public function getAddressType(): ?string {
		if ( $this->featureToggle->featureIsActive( 'campaigns.address_type.no_preselection' ) ) {
			return null;
		} elseif ( $this->featureToggle->featureIsActive( 'campaigns.address_type.preselection' ) ) {
			return 'person';
		}
		throw new UnknownChoiceDefinition( 'Address type configuration failure.' );
	}
}
