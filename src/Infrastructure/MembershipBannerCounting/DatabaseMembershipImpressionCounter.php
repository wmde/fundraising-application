<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\MembershipBannerCounting;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

/**
 * This is a temporary class to count impressions for the 2023/2024 thank you banner campaign.
 */
class DatabaseMembershipImpressionCounter implements MembershipImpressionCounter {
	public function __construct( private readonly Connection $db ) {
	}

	public function countImpressions( int $bannerImpressionCount, int $totalImpressionCount, string $tracking ): void {
		$this->db->insert(
			'membership_impression_count',
			[
				'banner_impression_count' => $bannerImpressionCount,
				'total_impression_count' => $totalImpressionCount,
				'tracking' => $tracking,
			],
			[
				'banner_impression_count' => ParameterType::INTEGER,
				'total_impression_count' => ParameterType::INTEGER,
				'tracking' => ParameterType::STRING,
			]
		);
	}
}
