<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Logging;

use DateTime;
use WMDE\Clock\Clock;
use WMDE\Fundraising\Frontend\BucketTesting\Domain\Model\Bucket;

/**
 * Logs the event info, the bucket data and the time as a JSON-encoded string
 */
class JsonBucketLogger implements BucketLogger {

	public function __construct(
		private readonly LogWriter $logWriter,
		private readonly Clock $clock
	) {
	}

	public function writeEvent( LoggingEvent $event, Bucket ...$buckets ): void {
		$result = json_encode( $this->formatData( $event, ...$buckets ) );
		if ( $result === false ) {
			throw new \RuntimeException( sprintf( "Failed to get JSON representation of: %s",
				var_export( $this->formatData( $event, ...$buckets ), true ) ) );
		}
		$this->logWriter->write( $result );
	}

	private function formatData( LoggingEvent $event, Bucket ...$buckets ): array {
		return [
			'date' => $this->clock->now()->format( DateTime::RFC3339_EXTENDED ),
			'eventName' => $event->getName(),
			'metadata' => $event->getMetaData(),
			'buckets' => $this->getBucketMap( $buckets )
		];
	}

	private function getBucketMap( array $buckets ): array {
		return array_reduce(
			$buckets,
			static function ( array $bucketMap, Bucket $bucket ) {
				$bucketMap[$bucket->getCampaign()->getName()] = $bucket->getName();
				return $bucketMap;
			},
			[]
		);
	}

}
