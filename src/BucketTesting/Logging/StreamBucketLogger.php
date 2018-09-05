<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Logging;

use WMDE\Fundraising\Frontend\BucketTesting\Bucket;

class StreamBucketLogger implements BucketLogger {

	private $url;
	private $stream;
	private $timeTeller;

	public function __construct( string $url, TimeTeller $timeTeller ) {
		$this->url = $url;
		$this->timeTeller = $timeTeller;
	}

	public function writeEvent( LoggingEvent $event, Bucket ...$buckets ): void {
		$this->openStreamIfNeeded();
		fwrite(
			$this->stream,
			json_encode( $this->formatData( $event, ...$buckets ) ) . "\n"
		);
	}

	private function openStreamIfNeeded() {
		if ( $this->stream !== null ) {
			return;
		}
		$this->createPathIfNeeded();
		$this->stream = @fopen( $this->url, 'a' );
		if ( $this->stream === false ) {
			throw new LoggingError( 'Could not open ' . $this->url );
		}
	}

	private function createPathIfNeeded() {
		$path = dirname( $this->url );
		if ( file_exists( $path ) ) {
			return;
		}
		if ( !mkdir( $path, 0777, true ) ) {
			throw new LoggingError( 'Could not create directory ' . $path );
		}
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
