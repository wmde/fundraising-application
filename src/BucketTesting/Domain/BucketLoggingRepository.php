<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Domain;

use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\BucketLog;

interface BucketLoggingRepository {

	public function storeBucketLog( BucketLog $bucketLog ): void;
}
