<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Twig_Environment;
use WMDE\Fundraising\Frontend\BucketTesting\FeatureToggle;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

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

	public function getConfirmationPageTemplate( Twig_Environment $twig, array $context ): TwigTemplate {
		if (
			$this->featureToggle->featureIsActive( 'campaigns.donation_address.required' ) ||
			$this->getSkinTemplateDirectory() !== $this->getSkinDirectory( 'cat17' )
		) {
			/** The "optional address" feature is only implemented for cat17 */
			return new TwigTemplate( $twig, 'Donation_Confirmation.html.twig', array_merge( [
					'templateName' => 'collapsed_membership_form'
				], $context )
			);
		} elseif ( $this->featureToggle->featureIsActive( 'campaigns.donation_address.optional' ) ) {
			return new TwigTemplate( $twig, 'Donation_Confirmation_FeatureToggle_Address.html.twig', array_merge( [
					'templateName' => 'expanded_membership_form'
				], $context )
			);
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

	private function getSkinDirectory( string $skin ): string {
		return 'skins/' . $skin . '/templates';
	}
}