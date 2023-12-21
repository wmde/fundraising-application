<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\MembershipBannerCounting;

/**
 * This is a temporary interface to count impressions for the 2023/2024 thank you banner campaign.
 */
interface MembershipImpressionCounter {
	public function countImpressions( int $bannerImpressionCount, int $totalImpressionCount, string $tracking ): void;
}
