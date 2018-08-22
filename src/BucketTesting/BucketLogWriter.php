<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

interface BucketLogWriter {
	public function writeEvent( string $eventName, array $eventMetadata, Bucket ...$buckets );
}