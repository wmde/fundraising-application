<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters\DonationFormPresenter;

class ImpressionCounts {
	private int $totalImpressionCount;
	private int $singleBannerImpressionCount;

	public function __construct( int $totalImpressionCount, int $singleBannerImpressionCount ) {
		$this->totalImpressionCount = $totalImpressionCount;
		$this->singleBannerImpressionCount = $singleBannerImpressionCount;
	}

	public function getTotalImpressionCount(): int {
		return $this->totalImpressionCount;
	}

	public function getSingleBannerImpressionCount(): int {
		return $this->singleBannerImpressionCount;
	}

}
