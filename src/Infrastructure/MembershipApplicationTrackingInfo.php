<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MembershipApplicationTrackingInfo {

	private $totalImpressionCount;
	private $singleBannerImpressionCount;
	private $confirmationPage;
	private $confirmationPageCampaign;

	public function __construct( int $totalImpressionCount, int $singleBannerImpressionCount,
		string $confirmationPage, string $confirmationPageCampaign ) {
		$this->totalImpressionCount = $totalImpressionCount;
		$this->singleBannerImpressionCount = $singleBannerImpressionCount;
		$this->confirmationPage = $confirmationPage;
		$this->confirmationPageCampaign = $confirmationPageCampaign;
	}

}
