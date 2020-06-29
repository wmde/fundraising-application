<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\BucketTesting;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\BestEffortBucketLogger;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\BucketLogger;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\LoggingError;
use WMDE\Fundraising\Frontend\Tests\Fixtures\BucketLoggerSpy;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeBucketLoggingEvent;

class BestEffortBucketLoggerTest extends TestCase {

	public function testGivenASuccessFulLogger_itWillPassOnEvents() {
		$event = new FakeBucketLoggingEvent();
		$buckets = [];
		$bucketLogger = new BucketLoggerSpy();

		$safeLogger = new BestEffortBucketLogger(
			$bucketLogger,
			new NullLogger()
		);
		$safeLogger->writeEvent( $event, ...$buckets );
		$safeLogger->writeEvent( $event, ...$buckets );

		$this->assertSame( 2, $bucketLogger->getEventCount() );
		$this->assertSame( $event, $bucketLogger->getFirstEvent() );
	}

	public function testGivenAFailingLogger_itWillPassOnOnlyFirstEvent() {
		$event = new FakeBucketLoggingEvent();
		$buckets = [];
		/** @var BucketLogger&MockObject $bucketLogger */
		$bucketLogger = $this->createMock( BucketLogger::class );
		$bucketLogger->expects( $this->once() )->method( 'writeEvent' )->willThrowException( new LoggingError() );

		$safeLogger = new BestEffortBucketLogger(
			$bucketLogger,
			new NullLogger()
		);
		$safeLogger->writeEvent( $event, ...$buckets );
		$safeLogger->writeEvent( $event, ...$buckets );
		$safeLogger->writeEvent( $event, ...$buckets );
	}

	public function testGivenAFailingLogger_itWillLogExceptionToLoggerOnce() {
		$event = new FakeBucketLoggingEvent();
		$buckets = [];
		$exception = new LoggingError( 'Could not open bucket.log' );
		/** @var BucketLogger&MockObject $bucketLogger */
		$bucketLogger = $this->createMock( BucketLogger::class );
		$bucketLogger->method( 'writeEvent' )->willThrowException( $exception );
		/** @var LoggerInterface&MockObject $errorLog */
		$errorLog = $this->createMock( LoggerInterface::class );
		$errorLog->expects( $this->once() )->method( 'error' )->with( 'Could not open bucket.log', $this->anything() );

		$safeLogger = new BestEffortBucketLogger(
			$bucketLogger,
			$errorLog
		);
		$safeLogger->writeEvent( $event, ...$buckets );
		$safeLogger->writeEvent( $event, ...$buckets );
		$safeLogger->writeEvent( $event, ...$buckets );
	}

}
