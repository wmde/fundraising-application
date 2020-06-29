<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Logging;

/**
 * Writes log entries to a file stream, one per line
 */
class StreamLogWriter implements LogWriter {

	private $url;
	private $stream;

	public function __construct( string $url ) {
		$this->url = $url;
	}

	/**
	 * @param string $logEntry
	 * @throws LoggingError
	 */
	public function write( string $logEntry ): void {
		$this->openStreamIfNeeded();
		fwrite(
			$this->stream,
			$logEntry . "\n"
		);
	}

	private function openStreamIfNeeded() {
		if ( $this->stream !== null ) {
			return;
		}
		$this->createPathIfNeeded();
		try {
			$this->stream = fopen( $this->url, 'a' );
		} catch ( \Exception $e ) {
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

	public function __destruct() {
		if ( is_resource( $this->stream ) ) {
			try {
				fclose( $this->stream );
			} catch ( \Exception $e ) {
				throw new LoggingError( 'Could not close log file properly' );
			}
		}
	}

}
