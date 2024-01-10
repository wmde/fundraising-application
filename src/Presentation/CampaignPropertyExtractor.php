<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Campaign;

class CampaignPropertyExtractor {

	/**
	 * @return string[]
	 */
	public static function listURLKeys( Campaign ...$campaigns ): array {
		return array_map(
			static function ( Campaign $c ) {
				return $c->getUrlKey();
			},
			$campaigns
		);
	}

}
