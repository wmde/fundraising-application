<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Factories;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use org\bovigo\vfs\vfsStream;
use WMDE\Fundraising\Frontend\Factories\LoggerFactory;
use PHPUnit\Framework\TestCase;

class LoggerFactoryTest extends TestCase {
	public function testGivenErrorHandlerConfiguration_itReturnsErrorLoggingHandler() {
		$factory = new LoggerFactory( [
			'method' => 'error_log',
			'level' => 'WARNING'
		]);
		$logger = $factory->getLogger();
		$this->assertCount( 1, $logger->getHandlers() );
		$firstHandler = $logger->getHandlers()[0]; /** @var StreamHandler $firstHandler */
		$this->assertInstanceOf( ErrorLogHandler::class, $firstHandler );
		$this->assertSame( Logger::WARNING, $firstHandler->getLevel() );
	}

	public function testGivenFileConfiguration_itReturnsStreamHandler() {
		vfsStream::setup( 'logs' );
		$factory = new LoggerFactory( [
			'method' => 'file',
			'url' => vfsStream::url( 'logs/error.log' ),
			'level' => 'ERROR'
		]);
		$logger = $factory->getLogger();
		$this->assertCount( 1, $logger->getHandlers() );
		$firstHandler = $logger->getHandlers()[0]; /** @var StreamHandler $firstHandler */
		$this->assertInstanceOf( StreamHandler::class, $firstHandler );
		$this->assertSame( Logger::ERROR, $firstHandler->getLevel() );
		$this->assertSame( vfsStream::url( 'logs/error.log' ), $firstHandler->getUrl() );
	}

	public function testGivenUnknownLogType_exeptionIsThrown() {
		$factory = new LoggerFactory( [
			'method' => 'syslog'
		]);
		$this->expectException( \InvalidArgumentException::class );
		$factory->getLogger();

	}

}
