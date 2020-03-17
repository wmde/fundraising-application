<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\BucketTesting\FeatureToggle;
use WMDE\Fundraising\Frontend\Infrastructure\UrlGenerator;

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

	public function getUseOfFundsResponse( FunFunFactory $factory ): \Closure {
		if ( $this->featureToggle->featureIsActive( 'campaigns.skins.laika' ) ) {
			$factory->getTranslationCollector()->addTranslationFile( $factory->getI18nDirectory() . '/messages/useOfFundsMessages.json' );
			$template = $factory->getLayoutTemplate( 'Funds_Usage.html.twig', [
				'use_of_funds_content' => $factory->getApplicationOfFundsContent(),
				'use_of_funds_messages' => $factory->getApplicationOfFundsMessages()
			] );
			return function() use ( $template )  {
				return $template->render( [] );
			};
		} elseif ( $this->featureToggle->featureIsActive( 'campaigns.skins.test' ) ) {
			// we don't care what happens in test
			return function() {
				return 'Test rendering: Use of funds';
			};
		}
		throw new UnknownChoiceDefinition( 'Use of funds configuration failure.' );
	}

	public function getMembershipCallToActionTemplate(): string {
		if ( $this->featureToggle->featureIsActive( 'campaigns.membership_call_to_action.regular' ) ) {
			return 'partials/donation_confirmation/feature_toggle/call_to_action_regular.html.twig';
		} elseif ( $this->featureToggle->featureIsActive( 'campaigns.membership_call_to_action.video' ) ) {
			return 'partials/donation_confirmation/feature_toggle/call_to_action_video.html.twig';
		}
		throw new UnknownChoiceDefinition( 'Membership Call to Action Template configuration failure.' );
	}

	public function getAmountOption(): array {
		if ( $this->featureToggle->featureIsActive( 'campaigns.amount_options.5to300_0' ) ) {
			return $this->getAmountOptionInEuros( [ 500, 1500, 2500, 5000, 7500, 10000, 25000, 30000 ] );
		} elseif ( $this->featureToggle->featureIsActive( 'campaigns.amount_options.5to300' ) ) {
			return $this->getAmountOptionInEuros( [ 500, 1500, 2500, 5000, 7500, 10000, 25000, 30000 ] );
		} elseif ( $this->featureToggle->featureIsActive( 'campaigns.amount_options.5to100' ) ) {
			return $this->getAmountOptionInEuros( [ 500, 1000, 1500, 2000, 3000, 5000, 7500, 10000 ] );
		} elseif ( $this->featureToggle->featureIsActive( 'campaigns.amount_options.15to250' ) ) {
			return $this->getAmountOptionInEuros( [ 1500, 2000, 2500, 3000, 5000, 7500, 10000, 25000 ] );
		} elseif ( $this->featureToggle->featureIsActive( 'campaigns.amount_options.30to250' ) ) {
			return $this->getAmountOptionInEuros( [ 3000, 4000, 5000, 7500, 10000, 15000, 20000, 25000 ] );
		} elseif ( $this->featureToggle->featureIsActive( 'campaigns.amount_options.50to500' ) ) {
			return $this->getAmountOptionInEuros( [ 5000, 10000, 15000, 20000, 25000, 30000, 50000 ] );
		}
		throw new UnknownChoiceDefinition( 'Amount option selection configuration failure.' );
	}

	public function getAmountOptionInEuros( array $amountOption ): array {
		return array_map( function ( int $amount ) {
			return Euro::newFromCents( $amount );
		}, $amountOption );
	}
}