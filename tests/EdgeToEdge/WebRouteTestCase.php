<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;
use PHPUnit\Framework\TestCase;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
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
	 * @param bool $debug
	 * @param array $serverConfig
	 *
	 * @return Client
	 */
	protected function createClient( array $config = [], callable $onEnvironmentCreated = null, bool $debug = true,
								  array $serverConfig = [] ): Client {
		$testEnvironment = TestEnvironment::newInstance( $config );

		if ( is_callable( $onEnvironmentCreated ) ) {
			call_user_func( $onEnvironmentCreated, $testEnvironment->getFactory(), $testEnvironment->getConfig() );
		}

		return new Client(
			$this->createApplication( $testEnvironment->getFactory(), $debug ),
			$serverConfig
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
			$this->createApplication( $testEnvironment->getFactory(), self::ENABLE_DEBUG )
		);

		call_user_func(
			$onEnvironmentCreated,
			$client,
			$testEnvironment->getFactory()
		);
	}

	/**
	 * Initializes a new test environment and Silex Application.
	 * Invokes the provided callable with a HttpKernel client to make requests to the application
	 * as first argument. The second argument is the top level factory which can be used for
	 * both setup before requests to the client and validation tasks afterwards. The third argument
	 * is the application instance itself.
	 *
	 * Use instead of createEnvironment when the application instance is needed.
	 *
	 * @param array $config
	 * @param callable $onEnvironmentCreated
	 */
	protected function createAppEnvironment( array $config, callable $onEnvironmentCreated ): void {
		$testEnvironment = TestEnvironment::newInstance( $config );

		$application = $this->createApplication( $testEnvironment->getFactory(), self::ENABLE_DEBUG );
		$client = new Client( $application );

		call_user_func(
			$onEnvironmentCreated,
			$client,
			$testEnvironment->getFactory(),
			$application
		);
	}

	// @codingStandardsIgnoreStart
	private function createApplication( FunFunFactory $ffFactory, bool $debug ): Application {
		// @codingStandardsIgnoreEnd
		$app = require __DIR__ . ' /../../app/bootstrap.php';

		if ( $debug ) {
			$app['debug'] = true;
			$app['session.test'] = true;
			unset( $app['exception_handler'] );
		}

		$ffFactory->setSkinTwigEnvironment( $app['twig'] );

		return $app;
	}

	protected function createSilexApplication(): Application {
		$testEnvironment = TestEnvironment::newInstance( [] );

		return $this->createApplication( $testEnvironment->getFactory(), self::ENABLE_DEBUG );
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

	protected function assertGetRequestCausesMethodNotAllowedResponse( string $route, array $params ): void {
		$client = $this->createClient();

		$this->expectException( MethodNotAllowedHttpException::class );
		$client->request( 'GET', $route, $params );
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
