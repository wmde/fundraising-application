<?php
declare(strict_types=1);

namespace Unit\Infrastructure;

use FileFetcher\FileFetcher;
use FileFetcher\FileFetchingException;
use FileFetcher\InMemoryFileFetcher;
use Psr\Log\LoggerInterface;
use WMDE\Fundraising\Frontend\Infrastructure\ErrorLoggingFileFetcher;
use PHPUnit\Framework\TestCase;

class ErrorLoggingFileFetcherTest extends TestCase {

	public function testGivenSucceedingFileFetcher_itsContentIsReturned() {
		$logger = $this->createMock( LoggerInterface::class );
		$logger->method( 'error' )->with( $this->never() );
		$errorLoggingFileFetcher = new ErrorLoggingFileFetcher(
			new InMemoryFileFetcher( ['song.txt' => 'I\'m a little teapot'] ),
			$logger
		);
		$this->assertSame( 'I\'m a little teapot', $errorLoggingFileFetcher->fetchFile( 'song.txt' ) );
	}

	public function testGivenFailingFileFetcher_anEmptyStringIsReturned() {
		$logger = $this->createMock( LoggerInterface::class );
		$wrappedFetcher = $this->createMock( FileFetcher::class );
		$wrappedFetcher->method( 'fetchFile' )->willThrowException( new FileFetchingException( 'song.txt' ) );
		$errorLoggingFileFetcher = new ErrorLoggingFileFetcher(
			new InMemoryFileFetcher( [] ),
			$logger
		);
		$this->assertSame( '', $errorLoggingFileFetcher->fetchFile( 'song.txt' ) );
	}

	public function testGivenFailingFileFetcher_theExceptionIsLogged() {
		$exception = new FileFetchingException( 'song.txt' );
		$wrappedFetcher = $this->createMock( FileFetcher::class );
		$wrappedFetcher->method( 'fetchFile' )->willThrowException( $exception );

		$logger = $this->createMock( LoggerInterface::class );
		$logger->expects( $this->once() )
			->method( 'error' )
			->with( $exception->getMessage(), $this->anything() );

		$errorLoggingFileFetcher = new ErrorLoggingFileFetcher(
			new InMemoryFileFetcher( [] ),
			$logger
		);
		$errorLoggingFileFetcher->fetchFile( 'song.txt' );
	}

}
