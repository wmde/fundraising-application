<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Logging;

use WMDE\Fundraising\Frontend\BucketTesting\Bucket;

class StreamBucketLogger implements BucketLogger {

	private $url;
	private $timeTeller;

	public function __construct( string $url, TimeTeller $timeTeller ) {
		$this->url = $url;
		$this->timeTeller = $timeTeller;
	}

	public function writeEvent( LoggingEvent $event, Bucket ...$buckets ): void {
		( new StreamLogWriter( $this->url ) )->write( json_encode( $this->formatData( $event, ...$buckets ) ) );
	}

	private function formatData( LoggingEvent $event, Bucket ...$buckets ): array {
		return [
			'date' => $this->timeTeller->getTime(),
			'eventName' => $event->getName(),
			'metadata' => $event->getMetaData(),
			'buckets' => $this->getBucketMap( $buckets )
		];
	}

	private function getBucketMap( array $buckets ): array {
		return array_reduce(
			$buckets,
			function ( array $bucketMap, Bucket $bucket ) {
				$bucketMap[$bucket->getCampaign()->getName()] = $bucket->getName();
				return $bucketMap;
			},
			[]
		);
	}

}
