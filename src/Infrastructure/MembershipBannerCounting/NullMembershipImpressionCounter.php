<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\MembershipBannerCounting;

class NullMembershipImpressionCounter implements MembershipImpressionCounter {
	public function countImpressions( int $bannerImpressionCount, int $totalImpressionCount, string $tracking ): void {
		// Do nothing
	}
}
