<?php

declare(strict_types = 1);

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

class SupportHandlerTest extends TestCase {

	public function testGivenWrappedMainHandlerHandlesRecord_loggerForHandlerErrorsLogsNothing(): void {
		/** @var LoggerInterface&MockObject $loggerForHandlerErrors */
		$loggerForHandlerErrors = $this->createMock( LoggerInterface::class );
		$loggerForHandlerErrors->expects( $this->never() )->method( $this->anything() );
		$supportHandler = new SupportHandler( new NullHandler(), $loggerForHandlerErrors );
		$supportHandler->handle( [ 'level'=>LogLevel::CRITICAL ] );
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

	public function testSupportHandlerReturnsHandlingResultFromWrappedMainHandler(): void {
		/** @var AbstractHandler&MockObject $returningHandler */
		$returningHandler = $this->createMock( AbstractHandler::class );

		$returningHandler->expects( $this->at( 0 ) )
			->method( 'handle' )
			->willReturn( false );

		$returningHandler->expects( $this->at( 1 ) )
			->method( 'handle' )
			->willReturn( true );

		$supportHandler = new SupportHandler( $returningHandler, new NullLogger() );
		$firstCallResult = $supportHandler->handle( [] );
		$secondCallResult = $supportHandler->handle( [] );

		$this->assertFalse( $firstCallResult );
		$this->assertTrue( $secondCallResult );
	}

}
