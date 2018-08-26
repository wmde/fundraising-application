<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Logging;

use WMDE\Fundraising\Frontend\BucketTesting\Bucket;

class NullBucketLogger implements BucketLogger {
	public function writeEvent( string $eventName, array $eventMetadata, Bucket ...$buckets ) {
		// do nothing
	}
}
