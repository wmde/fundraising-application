<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

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

	public function getMainCss(): string {
		if ( $this->featureToggle->featureIsActive( 'campaigns.donation_form_design.design_change' ) ) {
			return '/skins/cat17/css/variant.css';
		} elseif ( $this->featureToggle->featureIsActive( 'campaigns.donation_form_design.default' ) ) {
			return '/skins/cat17/css/main.css';
		}
		throw new UnknownChoiceDefinition( 'Design selection failure.' );
	}

	public function getMainMenuItems( UrlGenerator $urlGenerator ): array {
		if ( $this->featureToggle->featureIsActive( 'campaigns.donation_form_design.design_change' ) ) {
			return [
				[
					'url' => $urlGenerator->generateRelativeUrl( 'list-comments.html' ),
					'id' => 'comments-list',
					'label' => 'menu_item_donation_comments'
				],
				[
					'url' => $urlGenerator->generateRelativeUrl( 'faq' ),
					'id' => 'faq',
					'label' => 'menu_item_faq'
				],
				[
					'url' => $urlGenerator->generateRelativeUrl( 'use-of-funds' ),
					'id' => 'use_of_resources',
					'label' => 'menu_item_use_of_resources'
				],
				[
					'url' => $urlGenerator->generateRelativeUrl( 'show-donation-form' ),
					'id' => 'donation',
					'label' => 'menu_item_donate'
				],
			];
		} elseif ( $this->featureToggle->featureIsActive( 'campaigns.donation_form_design.default' ) ) {
			return [
				[
					'url' => $urlGenerator->generateRelativeUrl( 'list-comments.html' ),
					'id' => 'comments-list',
					'label' => 'menu_item_donation_comments'
				],
				[
					'url' => $urlGenerator->generateRelativeUrl( 'faq' ),
					'id' => 'faq',
					'label' => 'menu_item_faq'
				],
				[
					'url' => $urlGenerator->generateRelativeUrl( 'use-of-funds' ),
					'id' => 'use_of_resources',
					'label' => 'menu_item_use_of_resources'
				],
				[
					'url' => $urlGenerator->generateRelativeUrl( 'page', ['pageName' => 'Spendenquittung'] ),
					'id' => 'donation_receipt',
					'label' => 'menu_item_donation_receipt'
				],
			];
		}
		throw new UnknownChoiceDefinition( 'Design selection failure.' );
	}

	private function getSkinDirectory( string $skin ): string {
		return 'skins/' . $skin . '/templates';
	}

	public function getAmountOptionInEuros( array $amountOption ): array {
		return array_map( function ( int $amount ) {
			return Euro::newFromCents( $amount );
		}, $amountOption );
	}
}