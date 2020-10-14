<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use Monolog\Handler\AbstractHandler;
use Monolog\Handler\NullHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use WMDE\Fundraising\Frontend\Infrastructure\SupportHandler;
use WMDE\PsrLogTestDoubles\LoggerSpy;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\SupportHandler
 */
class SupportHandlerTest extends TestCase {

	public function testGivenWrappedMainHandlerHandlesRecord_loggerForHandlerErrorsLogsNothing(): void {
		/** @var LoggerInterface&MockObject $loggerForHandlerErrors */
		$loggerForHandlerErrors = $this->createMock( LoggerInterface::class );
		$loggerForHandlerErrors->expects( $this->never() )->method( $this->anything() );
		$supportHandler = new SupportHandler( new NullHandler(), $loggerForHandlerErrors );
		$supportHandler->handle( [ 'level' => LogLevel::CRITICAL ] );
	}

	public function testGivenWrappedMainHandlerThrowsException_loggerForHandlerErrorsLogsThisException(): void {
		/** @var AbstractHandler&MockObject $throwingHandler */
		$throwingHandler = $this->createMock( AbstractHandler::class );
		$throwingHandler->method( 'handle' )->willThrowException( new \RuntimeException( 'I can\'t handle the logs anymore ?!😞' ) );

		$loggerForHandlerErrors = new LoggerSpy();

		$supportHandler = new SupportHandler( $throwingHandler, $loggerForHandlerErrors );
		$wasHandled = $supportHandler->handle( [] );

		$firstLogCall = $loggerForHandlerErrors->getFirstLogCall();
		$this->assertNotNull( $firstLogCall );
		$this->assertEquals( 'I can\'t handle the logs anymore ?!😞', $firstLogCall->getMessage() );
		$this->assertEquals( LogLevel::ERROR, $firstLogCall->getLevel() );
		$this->assertFalse( $wasHandled, 'Support handler should always return false when error-prone handler has thrown an exception.' );
	}

	/**
	 * @dataProvider handlerResultsProvider
	 *
	 * @param bool $returnValue
	 */
	public function testSupportHandlerReturnsHandlingResultFromWrappedMainHandler( bool $returnValue ): void {
		/** @var AbstractHandler&MockObject $returningHandler */
		$returningHandler = $this->createMock( AbstractHandler::class );
		$returningHandler->method( 'handle' )
			->willReturn( $returnValue );
		$supportHandler = new SupportHandler( $returningHandler, new NullLogger() );

		$callResult = $supportHandler->handle( [] );

		$this->assertSame( $returnValue, $callResult );
	}

	public function handlerResultsProvider(): iterable {
		yield [ true ];
		yield [ false ];
	}

}
