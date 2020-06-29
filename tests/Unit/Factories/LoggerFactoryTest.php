<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Factories;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Factories\LoggerFactory;
use WMDE\Fundraising\Frontend\Infrastructure\SupportHandler;

class LoggerFactoryTest extends TestCase {
	public function testGivenErrorHandlerConfiguration_itReturnsErrorLoggingHandler(): void {
		$factory = new LoggerFactory( [
			'handlers' => [
				[
					'method' => 'error_log',
					'level' => 'WARNING'
				]
			]
		] );
		$logger = $factory->getLogger();
		$this->assertCount( 1, $logger->getHandlers() );
		$firstHandler = $logger->getHandlers()[0]; /** @var StreamHandler $firstHandler */
		$this->assertInstanceOf( ErrorLogHandler::class, $firstHandler );
		$this->assertSame( Logger::WARNING, $firstHandler->getLevel() );
	}

	public function testGivenFileConfiguration_itReturnsStreamHandler(): void {
		vfsStream::setup( 'logs' );
		$factory = new LoggerFactory( [
			'handlers' => [
				[
					'method' => 'file',
					'url' => vfsStream::url( 'logs/error.log' ),
					'level' => 'ERROR'
				]
			]
		] );
		$logger = $factory->getLogger();
		$this->assertCount( 1, $logger->getHandlers() );
		$firstHandler = $logger->getHandlers()[0]; /** @var StreamHandler $firstHandler */
		$this->assertInstanceOf( StreamHandler::class, $firstHandler );
		$this->assertSame( Logger::ERROR, $firstHandler->getLevel() );
		$this->assertSame( vfsStream::url( 'logs/error.log' ), $firstHandler->getUrl() );
	}

	public function testGivenErrbitLoggingConfiguration_itReturnsErrbitLoggingHandler(): void {
		$factory = new LoggerFactory( [
			'handlers' => [
				[
					'method' => 'errbit',
					'level' => 'ERROR',
					'projectId' => 1,
					'projectKey' => 'fd8794457af0da70882c850eb486524f',
					'host' => 'http://errbit:8080'
				]
			],
		] );
		$logger = $factory->getLogger();
		$this->assertCount( 1, $logger->getHandlers() );
		$firstHandler = $logger->getHandlers()[0]; /** @var SupportHandler $firstHandler */
		$this->assertInstanceOf( SupportHandler::class, $firstHandler );
		$this->assertSame( Logger::ERROR, $firstHandler->getLevel() );
	}

	public function missingErrbitConfigParamsProvider(): iterable {
		$validParams = [
			'projectId' => 1,
			'projectKey' => 'fd8794457af0da70882c850eb486524f',
			'host' => 'http://errbit:8080'
		];
		yield [ array_merge( $validParams, [ 'projectId' => '' ] ) ];
		yield [ array_merge( $validParams, [ 'projectKey' => null ] ) ];
		yield [ array_merge( $validParams, [ 'host' => '' ] ) ];
		yield [ [
		'projectId' => '',
			'projectKey' => '',
			'host' => ''
		] ];
	}

	/**
	 * @dataProvider missingErrbitConfigParamsProvider
	 */
	public function testMissingErrbitConfigParam_itThrowsException( array $invalidConfigParams ): void {
		$factory = new LoggerFactory( [
			'handlers' => [
				[
					'method' => 'errbit',
					'level' => 'ERROR',
					'projectId' => $invalidConfigParams['projectId'],
					'projectKey' => $invalidConfigParams['projectKey'],
					'host' => $invalidConfigParams['host']
				]
			],
		] );
		$this->expectException( \InvalidArgumentException::class );
		$logger = $factory->getLogger();
	}

	public function testGivenUnknownLogType_exceptionIsThrown(): void {
		$factory = new LoggerFactory( [
			'handlers' => [
				[ 'method' => 'nonExistingInvalidHandler' ]
			]
		] );
		$this->expectException( \InvalidArgumentException::class );
		$factory->getLogger();
	}

	public function testGivenMultipleValidHandlers_itReturnsMultipleHandlers(): void {
		vfsStream::setup( 'logs' );
		$factory = new LoggerFactory( [
			'handlers' => [
				[
					'method' => 'file',
					'url' => vfsStream::url( 'logs/error.log' ),
					'level' => 'ERROR'
				],
				[
					'method' => 'error_log',
					'level' => 'WARNING'
				]
			],
		] );
		$logger = $factory->getLogger();
		$this->assertCount( 2, $logger->getHandlers() );
		$firstHandler = $logger->getHandlers()[0]; /** @var StreamHandler $firstHandler */
		$this->assertInstanceOf( StreamHandler::class, $firstHandler );
		$this->assertSame( Logger::ERROR, $firstHandler->getLevel() );
		$this->assertSame( vfsStream::url( 'logs/error.log' ), $firstHandler->getUrl() );

		$secondHandler = $logger->getHandlers()[1]; /** @var ErrorLogHandler $secondHandler */
		$this->assertInstanceOf( ErrorLogHandler::class, $secondHandler );
		$this->assertSame( Logger::WARNING, $secondHandler->getLevel() );
	}

	public function testGivenMultipleHandlersOneInvalid_itThrowsException(): void {
		$factory = new LoggerFactory( [
			'handlers' => [
				[
					'method' => 'nonExistingInvalidHandler',
					'level' => 'ERROR'
				],
				[
					'method' => 'error_log',
					'level' => 'WARNING'
				]
			],
		] );

		$this->expectException( \InvalidArgumentException::class );
		$factory->getLogger();
	}
}
