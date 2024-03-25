<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationFormPresenter\ImpressionCounts;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\MembershipContext\Domain\Model\Incentive;

class MembershipApplicationFormPresenter {

	/**
	 * @param TwigTemplate $template
	 * @param Incentive[] $incentives
	 */
	public function __construct(
		private readonly TwigTemplate $template,
		private readonly array $incentives
	) {
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
