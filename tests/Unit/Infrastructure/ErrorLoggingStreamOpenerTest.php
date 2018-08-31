<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use WMDE\Fundraising\Frontend\Infrastructure\ErrorLoggingStreamOpener;
use WMDE\Fundraising\Frontend\Infrastructure\StreamOpener;
use WMDE\Fundraising\Frontend\Infrastructure\StreamOpeningError;

class ErrorLoggingStreamOpenerTest extends TestCase {
	public function testGivenSuccessfulStreamOpener_itReturnsOpenedStream() {
		$stream = fopen( 'php://memory', 'w' );
		$wrappedOpener = $this->createMock( StreamOpener::class );
		$wrappedOpener->method( 'openStream' )->willReturn( $stream );
		$loggingOpener = new ErrorLoggingStreamOpener( $wrappedOpener, new NullLogger() );

		$this->assertSame( $stream, $loggingOpener->openStream( 'php://memory', 'w' ) );
	}

	public function testGivenFailingStreamOpener_itReturnsFallbackStream() {
		$wrappedOpener = $this->createMock( StreamOpener::class );
		$wrappedOpener->method( 'openStream' )->willThrowException( new StreamOpeningError() );
		$loggingOpener = new ErrorLoggingStreamOpener( $wrappedOpener, new NullLogger() );

		$fallbackStream = $loggingOpener->openStream( '/tmp/testfile', 'w' );
		$metadata = stream_get_meta_data( $fallbackStream );

		$this->assertTrue( is_resource( $fallbackStream ) );
		$this->assertArrayHasKey( 'uri', $metadata );
		$this->assertNotSame( '/tmp/testfile', $metadata['uri'] );
	}

	public function testGivenFailingStreamOpener_exceptionIsLogged() {
		$wrappedOpener = $this->createMock( StreamOpener::class );
		$wrappedOpener->method( 'openStream' )->willThrowException( new StreamOpeningError( 'Something went wrong' ) );
		$logger = $this->createMock( LoggerInterface::class );
		$logger->expects( $this->once() )
			->method( 'error' )
			->with(
				$this->equalTo( 'Something went wrong' ),
				$this->equalTo( [ 'url' => '/tmp/testfile', 'mode' => 'w'] )
			);
		$loggingOpener = new ErrorLoggingStreamOpener( $wrappedOpener, $logger );

		$loggingOpener->openStream( '/tmp/testfile', 'w' );
	}
}
