<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use PHPUnit\Framework\TestCase;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Frontend\App\Bootstrap;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @license GPL-2.0-or-later
 */
abstract class WebRouteTestCase extends TestCase {

	protected const DISABLE_DEBUG = false;
	protected const ENABLE_DEBUG = true;

	/**
	 * Initializes a new test environment and Silex Application and returns a HttpKernel client to
	 * make requests to the application.
	 *
	 * @param array $config
	 * @param callable|null $onEnvironmentCreated Gets called after onTestEnvironmentCreated, same signature
	 *
	 * @return Client
	 */
	protected function createClient( array $config = [], callable $onEnvironmentCreated = null ): Client {
		$testEnvironment = TestEnvironment::newInstance( $config );

		if ( is_callable( $onEnvironmentCreated ) ) {
			call_user_func( $onEnvironmentCreated, $testEnvironment->getFactory(), $testEnvironment->getConfig() );
		}

		return new Client(
			$this->createApplication( $testEnvironment->getFactory() )
		);
	}

	/**
	 * Initializes a new test environment and Silex Application.
	 * Invokes the provided callable with a HttpKernel client to make requests to the application
	 * as first argument. The second argument is the top level factory which can be used for
	 * both setup before requests to the client and validation tasks afterwards.
	 *
	 * Use instead of createClient when the client and factory are needed in the same scope.
	 *
	 * @param array $config
	 * @param callable $onEnvironmentCreated
	 */
	protected function createEnvironment( array $config, callable $onEnvironmentCreated ): void {
		$testEnvironment = TestEnvironment::newInstance( $config );

		$client = new Client(
			$this->createApplication( $testEnvironment->getFactory() )
		);

		call_user_func(
			$onEnvironmentCreated,
			$client,
			$testEnvironment->getFactory()
		);
	}

	// @codingStandardsIgnoreStart
	private function createApplication( FunFunFactory $ffFactory ): Application {
		// @codingStandardsIgnoreEnd
		$app = Bootstrap::initializeApplication( $ffFactory );

		$app['session.test'] = true;

		return $app;
	}

	protected function assert404( Response $response ): void {
		$this->assertSame( 404, $response->getStatusCode() );
	}

	protected function assertJsonResponse( array $expected, Response $response ): void {
		$this->assertSame(
			json_encode( $expected, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ),
			$response->getContent()
		);
	}

	protected function assertJsonSuccessResponse( array $expected, Response $response ): void {
		$this->assertTrue( $response->isSuccessful(), 'request is successful' );
		$this->assertJson( $response->getContent(), 'response is json' );
		$this->assertJsonResponse( $expected, $response );
	}

	protected function assertErrorJsonResponse( Response $response ): void {
		$responseData = $this->getJsonFromResponse( $response );
		$this->assertArrayHasKey( 'status', $responseData );
		$this->assertSame( 'ERR', $responseData['status'] );
		$this->assertThat(
			$responseData,
			$this->logicalOr(
				$this->arrayHasKey( 'message' ),
				$this->arrayHasKey( 'messages' )
			)
		);
	}

	protected function getJsonFromResponse( Response $response ): array {
		$this->assertJson( $response->getContent(), 'response is json' );
		return json_decode( $response->getContent(), true );
	}

	protected function assertSuccessJsonResponse( Response $response ): void {
		$responseData = $this->getJsonFromResponse( $response );
		$this->assertArrayHasKey( 'status', $responseData );
		$this->assertSame( 'OK', $responseData['status'] );
		$this->assertArrayHasKey( 'message', $responseData );
	}

	protected function assertInitialFormValues( array $expected, Client $client ): void {
		$initialFormValues = $client->getCrawler()->filter( 'script[data-initial-form-values]' );
		$this->assertGreaterThan(
			0,
			$initialFormValues->count()
		);
		$json = $initialFormValues->attr( 'data-initial-form-values' );
		$data = json_decode( $json, true );
		$this->assertEquals( $expected, $data );
	}
}
