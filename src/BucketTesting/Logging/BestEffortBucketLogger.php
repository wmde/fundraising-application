<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Logging;

use Psr\Log\LoggerInterface;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;

class BestEffortBucketLogger implements BucketLogger {

	private ?LoggingError $caughtException = null;

	public function __construct(
		private readonly BucketLogger $bucketLogger,
		private readonly LoggerInterface $errorLogging
	) {
	}

	public function writeEvent( LoggingEvent $event, Bucket ...$buckets ): void {
		if ( $this->caughtException !== null ) {
			return;
		}
		try {
			$this->bucketLogger->writeEvent( $event, ...$buckets );
		} catch ( LoggingError $error ) {
			$this->caughtException = $error;
			$this->errorLogging->error( $error->getMessage(), [ 'exception' => $error ] );
		}
	}

}
