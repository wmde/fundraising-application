<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Twig_Environment;
use WMDE\Fundraising\Frontend\BucketTesting\FeatureToggle;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

/**
 * Factory for generating classes whose implementations differ due to A/B testing.
 *
 * TODO:
 * Write static analyzer that compares calls to `grantsAccessTo` with the campaign configuration to make sure that
 * no mismatch occurs between configuration and code. Also make sure that grantsAccessTo with the default bucket is
 * always called last.
 *
 * @license GNU GPL v2+
 */
class ChoiceFactory {

	private $featureToggle;

	public function __construct( FeatureToggle $featureToggle ) {
		$this->featureToggle = $featureToggle;
	}

	/**
	 * Pass different parameters to the confirmation page template.
	 *
	 * The parameters will be used to track the effectiveness of membership applications, depending on the layout of the
	 * confirmation page.
	 *
	 * In the future, the actual saving of the choice might be done differently (with the general A/B testing storage
	 * mechanism instead of storing it in the membership data blob).
	 * Then the parameters should be removed and different templates used instead.
	 *
	 * @param Twig_Environment $twig
	 * @param array $context Additional template context variables
	 *
	 * @return TwigTemplate
	 */
	public function getConfirmationPageTemplate( Twig_Environment $twig, array $context ): TwigTemplate {
		if ( $this->featureToggle->featureIsActive( 'campaigns.confirmation_pages.collapsed_membership_form' ) ) {
			return new TwigTemplate( $twig, 'Donation_Confirmation.html.twig', array_merge( [
					'templateCampaign' => 'confirmation_pages',
					'templateName' => 'collapsed_membership_form'
				], $context )
			);
		} elseif ( $this->featureToggle->featureIsActive( 'campaigns.confirmation_pages.expanded_membership_form' ) ) {
			return new TwigTemplate( $twig, 'Donation_Confirmation.html.twig', array_merge( [
					'templateCampaign' => 'confirmation_pages',
					'templateName' => 'expanded_membership_form'
				], $context )
			);
		}
		throw new UnknownChoiceDefinition( 'Confirmation Page Template configuration failure.' );
	}

}