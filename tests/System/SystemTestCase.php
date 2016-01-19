<?php

namespace WMDE\Fundraising\Frontend\Tests\System;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Frontend\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class SystemTestCase extends \PHPUnit_Framework_TestCase {

	/**
	 * @var TestEnvironment
	 */
	protected $testEnvironment;

	/**
	 * Initializes a new test environment and Silex Application and returns a HttpKernel client to
	 * make requests to the application. The initialized test environment gets set to the
	 * $testEnvironment field.
	 *
	 * @param array $config
	 * @param callable|null $onEnvironmentCreated Gets called after onTestEnvironmentCreated, same signature
	 *
	 * @return Client
	 */
	public function createClient( array $config = [], callable $onEnvironmentCreated = null ): Client {
		$this->testEnvironment = TestEnvironment::newInstance( $config );

		$this->onTestEnvironmentCreated( $this->getFactory(), $this->getConfig() );

		if ( is_callable( $onEnvironmentCreated ) ) {
			call_user_func( $onEnvironmentCreated, $this->getFactory(), $this->getConfig() );
		}

		return new Client( $this->createApplication() );
	}

	/**
	 * Template method. No need to call the definition here from overriding methods as
	 * this one will always be empty.
	 *
	 * @param FunFunFactory $factory
	 * @param array $config
	 */
	protected function onTestEnvironmentCreated( FunFunFactory $factory, array $config ) {
		// No-op
	}

	private function createApplication() : Application {
		/** @noinspection PhpUnusedLocalVariableInspection */
		$ffFactory = $this->testEnvironment->getFactory();
		$app = require __DIR__ . ' /../../app/bootstrap.php';

		$app['debug'] = true;
		unset( $app['exception_handler'] );

		return $app;
	}

	protected function getFactory(): FunFunFactory {
		return $this->testEnvironment->getFactory();
	}

	protected function getConfig(): array {
		return $this->testEnvironment->getConfig();
	}

	protected function assert404( Response $response, $expectedMessage = 'Not Found' ) {
		$this->assertJson( $response->getContent(), 'response is json' );

		$this->assertJsonResponse(
			[
				'message' => $expectedMessage,
				'code' => 404,
			],
			$response
		);

		$this->assertSame( 404, $response->getStatusCode() );
	}

	private function assertJsonResponse( $expected, Response $response ) {
		$this->assertSame(
			json_encode( $expected, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ),
			$response->getContent()
		);
	}

	protected function assertJsonSuccessResponse( $expected, Response $response ) {
		$this->assertTrue( $response->isSuccessful(), 'request is successful' );
		$this->assertJson( $response->getContent(), 'response is json' );
		$this->assertJsonResponse( $expected, $response );
	}

}