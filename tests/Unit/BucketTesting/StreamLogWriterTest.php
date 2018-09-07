<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\LoggingError;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\LogWriter;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\StreamLogWriter;

/**
 * @covers \WMDE\Fundraising\Frontend\BucketTesting\Logging\StreamLogWriter
 */
class StreamLogWriterTest extends TestCase {

	private const EXISTING_DIRECTORY = 'logs';

	private $logPath;

	public function setUp() {
		vfsStream::setup( self::EXISTING_DIRECTORY );
		$this->logPath = self::EXISTING_DIRECTORY . '/buckets.log';
	}


	public function testHappyPath() {
		$logWriter = $this->newLogWriter();

		$logWriter->write( 'such string' );
		$logWriter->write( 'wow omg' );

		$this->assertSame(
			"such string\nwow omg\n",
			$this->getLogFileContents()
		);
	}

	private function newLogWriter(): LogWriter {
		return new StreamLogWriter( vfsStream::url( $this->logPath ) );
	}

	private function getLogFileContents(): string {
		return file_get_contents( vfsStream::url( $this->logPath ) );
	}

	public function testWhenOpeningTheUrlFails_anExceptionIsThrown() {
		$this->logPath = 'does/not/exist.log';

		$logWriter = $this->newLogWriter();

		$this->expectException( LoggingError::class );
		$logWriter->write( 'kaboom!' );
	}

	public function testWhenTargetPathDoesNotExist_itIsCreated() {
		$this->logPath = self::EXISTING_DIRECTORY . '/down/deep/bucket.log';

		$logWriter = $this->newLogWriter();

		$logWriter->write( 'such string' );
		$logWriter->write( 'wow omg' );

		$this->assertLogFileExists();
	}

	private function assertLogFileExists() {
		$this->assertFileExists( vfsStream::url( $this->logPath ) );
	}

}
