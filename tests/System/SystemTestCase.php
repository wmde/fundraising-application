<?php

namespace WMDE\Fundraising\Frontend\Tests\System;

use Silex\Application;
use Silex\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class SystemTestCase extends WebTestCase {

	/**
	 * @var TestEnvironment
	 */
	protected $testEnvironment;

	public function setUp() {
		$this->testEnvironment = TestEnvironment::newInstance();
		parent::setUp();
	}

	public function createApplication() : Application {
		/** @noinspection PhpUnusedLocalVariableInspection */
		$ffFactory = $this->testEnvironment->getFactory();
		$app = require __DIR__ . ' /../../app/bootstrap.php';

		$app['debug'] = true;
		unset( $app['exception_handler'] );

		return $app;
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

}