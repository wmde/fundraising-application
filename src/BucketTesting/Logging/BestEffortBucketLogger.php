<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Logging;

use Psr\Log\LoggerInterface;
use WMDE\Fundraising\Frontend\BucketTesting\Bucket;

/**
 * @license GNU GPL v2+
 */
class BestEffortBucketLogger implements BucketLogger {

	private $bucketLogger;
	private $errorLogging;
	private $caughtException;

	public function __construct( BucketLogger $bucketLogger, LoggerInterface $errorLogging ) {
		$this->bucketLogger = $bucketLogger;
		$this->errorLogging = $errorLogging;
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