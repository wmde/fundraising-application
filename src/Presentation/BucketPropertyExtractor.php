<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;

/**
 * Prepares a list of buckets into template variables
 */
class BucketPropertyExtractor {

	/**
	 * @return string[]
	 */
	public static function listBucketIds( Bucket ...$buckets ): array {
		return array_map(
			static function ( Bucket $b ) {
				return $b->getId();
			},
			$buckets
		);
	}

}
