<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters\DonationFormPresenter;

class ImpressionCounts {

	public function __construct(
		private readonly int $totalImpressionCount,
		private readonly int $singleBannerImpressionCount
	) {
	}

	public function getTotalImpressionCount(): int {
		return $this->totalImpressionCount;
	}

	public function getSingleBannerImpressionCount(): int {
		return $this->singleBannerImpressionCount;
	}

}
