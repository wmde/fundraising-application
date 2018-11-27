<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use WMDE\Fundraising\Frontend\BucketTesting\FeatureToggle;

/**
 * Factory for generating classes whose implementations differ due to A/B testing.
 *
 * @license GNU GPL v2+
 */
class ChoiceFactory {

	private $featureToggle;

	public function __construct( FeatureToggle $featureToggle ) {
		$this->featureToggle = $featureToggle;
	}

	public function isDonationAddressOptional(): bool {
		/** The "optional address" feature is only implemented for cat17 */
		if ( $this->getSkinTemplateDirectory() !== $this->getSkinDirectory( 'cat17' ) ) {
			return false;
		}
		if ( $this->featureToggle->featureIsActive( 'campaigns.donation_address.required' ) ) {
			return false;
		} elseif ( $this->featureToggle->featureIsActive( 'campaigns.donation_address.optional' ) ) {
			return true;
		}
		throw new UnknownChoiceDefinition( 'Confirmation Page Template configuration failure.' );
	}

	public function getSkinTemplateDirectory(): string {
		if ( $this->featureToggle->featureIsActive( 'campaigns.skins.cat17' ) ) {
			return $this->getSkinDirectory( 'cat17' );
		} elseif ( $this->featureToggle->featureIsActive( 'campaigns.skins.10h16' ) ) {
			return $this->getSkinDirectory( '10h16' );
		} elseif ( $this->featureToggle->featureIsActive( 'campaigns.skins.test' ) ) {
			return $this->getSkinDirectory( 'test' );
		}
		throw new UnknownChoiceDefinition( 'Skin selection configuration failure.' );
	}

	public function getMembershipCallToActionTemplate(): string {
		if ( $this->featureToggle->featureIsActive( 'campaigns.membership_call_to_action.regular' ) ) {
			return 'partials/donation_confirmation/feature_toggle/call_to_action_regular.html.twig';
		} elseif ( $this->featureToggle->featureIsActive( 'campaigns.membership_call_to_action.video' ) ) {
			return 'partials/donation_confirmation/feature_toggle/call_to_action_video.html.twig';
		}
		throw new UnknownChoiceDefinition( 'Membership Call to Action Template configuration failure.' );
	}

	private function getSkinDirectory( string $skin ): string {
		return 'skins/' . $skin . '/templates';
	}
}