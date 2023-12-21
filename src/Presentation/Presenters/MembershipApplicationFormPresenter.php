<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationFormPresenter\ImpressionCounts;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

class MembershipApplicationFormPresenter {
	private TwigTemplate $template;
	private array $incentives;

	public function __construct( TwigTemplate $template, array $incentives ) {
		$this->template = $template;
		$this->incentives = $incentives;
	}

	public function present( array $urls,
							bool $showMembershipTypeOption,
							array $initialDonationFormValues,
							array $initialValidationResult,
							ImpressionCounts $impressionCounts
	): string {
		return $this->template->render( [
			'urls' => $urls,
			'showMembershipTypeOption' => $showMembershipTypeOption,
			'initialFormValues' => array_merge( $initialDonationFormValues, [ 'incentives' => $this->incentives ] ),
			'initialValidationResult' => $initialValidationResult,
			'tracking' => [
				'bannerImpressionCount' => $impressionCounts->getSingleBannerImpressionCount(),
				'impressionCount' => $impressionCounts->getTotalImpressionCount()
			]
		] );
	}
}
