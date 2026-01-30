<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationFormPresenter\ImpressionCounts;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;

class MembershipApplicationFormPresenter {

	/**
	 * @param TwigTemplate $template
	 * @param string[] $incentives
	 */
	public function __construct(
		private readonly TwigTemplate $template,
		private readonly array $incentives
	) {
	}

	/**
	 * @param array<string, string> $urls
	 * @param bool $showMembershipTypeOption
	 * @param array<string, scalar> $initialDonationFormValues
	 * @param array<string, scalar> $validationResult
	 * @param ImpressionCounts $impressionCounts
	 */
	public function present(
		array $urls,
		bool $showMembershipTypeOption,
		array $initialDonationFormValues,
		array $validationResult,
		ImpressionCounts $impressionCounts
	): string {
		return $this->template->render( [
			'urls' => $urls,
			'showMembershipTypeOption' => $showMembershipTypeOption,
			'initialFormValues' => array_merge( $initialDonationFormValues, [ 'incentives' => $this->incentives ] ),
			'validationResult' => $validationResult,
			'tracking' => [
				'bannerImpressionCount' => $impressionCounts->getSingleBannerImpressionCount(),
				'impressionCount' => $impressionCounts->getTotalImpressionCount()
			]
		] );
	}
}
