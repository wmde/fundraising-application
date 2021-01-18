<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use PHPUnit\Framework\TestCase;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use WMDE\Fundraising\Frontend\App\Bootstrap;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\HttpKernelBrowser;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @license GPL-2.0-or-later
 */
abstract class WebRouteTestCase extends TestCase {

	protected const DISABLE_DEBUG = false;
	protected const ENABLE_DEBUG = true;

	protected Application $app;

	protected ?FunFunFactory $factory = null;

	protected array $applicationConfiguration = [];
	protected array $sessionValues = [];

	protected function tearDown(): void {
		parent::tearDown();
		$this->factory = null;
		$this->applicationConfiguration = [];
		$this->sessionValues = [];
	}

	/**
	 * Initializes a new test environment and returns a HttpKernel client to
	 * make requests to the application.
	 *
	 * @param array $config
	 * @param callable|null $onEnvironmentCreated Gets called after onTestEnvironmentCreated, same signature
	 *
	 * @return HttpKernelBrowser
	 */
	protected function createClient( array $config = [], callable $onEnvironmentCreated = null ): HttpKernelBrowser {
		if ( !empty( $config ) ) {
			$this->fail( "Adding a config is forbidden, use `modifyConfiguration` instead" );
		}
		if ( !empty( $onEnvironmentCreated ) ) {
			$this->fail( "using `onEnvironmentCreated` is deprecated, call `modifyEnvironment` instead" );
		}

		$app = $this->createApplication( $this->getFactory() );

		return new HttpKernelBrowser( $app );
	}

	/**
	 * Initializes a new test environment and HttpKernel.
	 *
	 * Invokes the provided callable with a HttpKernel client to make requests to the application
	 * as first argument. The second argument is the top level factory which can be used for
	 * both setup before requests to the client and validation tasks afterwards.
	 *
	 * Use this method only when you need the factory after the initial setup of the client - when calling
	 * modifyEnvironment before calling createClient is not sufficient. After the move to Symfony, calls to this
	 * method should be replaced with $this->getContainer('application_factory') and getClient
	 *
	 * @param array $config
	 * @param callable $onEnvironmentCreated
	 */
	protected function createEnvironment( array $config, callable $onEnvironmentCreated ): void {
		if ( !empty( $config ) ) {
			$this->fail( "Adding a config is forbidden, use `modifyConfiguration` instead" );
		}
		$testEnvironment = TestEnvironment::newInstance( $this->applicationConfiguration );

		$client = new HttpKernelBrowser(
			$this->createApplication( $testEnvironment->getFactory() )
		);

		call_user_func(
			$onEnvironmentCreated,
			$client,
			$testEnvironment->getFactory()
		);
	}

	protected function modifyConfiguration( array $config ) {
		$this->applicationConfiguration = $config;
	}

	protected function modifyEnvironment( callable $doModify ) {
		call_user_func( $doModify, $this->getFactory() );
	}

	private function getFactory(): FunFunFactory {
		if ( $this->factory === null ) {
			$this->factory = TestEnvironment::newInstance( $this->applicationConfiguration )->getFactory();
		}
		return $this->factory;
	}

	// @codingStandardsIgnoreStart
	private function createApplication( FunFunFactory $ffFactory ): Application {
		// @codingStandardsIgnoreEnd
		$this->app = Bootstrap::initializeApplication( $ffFactory );

		$this->app['session.test'] = true;
		$this->initializeApplicationSessionValues();

		return $this->app;
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

	protected function assertInitialFormValues( array $expected, HttpKernelBrowser $client ): void {
		$initialFormValues = $client->getCrawler()->filter( 'script[data-initial-form-values]' );
		$this->assertGreaterThan(
			0,
			$initialFormValues->count()
		);
		$json = $initialFormValues->attr( 'data-initial-form-values' );
		$data = json_decode( $json, true );
		$this->assertEquals( $expected, $data );
	}

	/**
	 * @todo Change code to work with Symfony DI when switch to Symfony is done
	 * @param string $key
	 * @return mixed
	 */
	protected function getSessionValue( string $key ) {
		if ( !( $this->app instanceof Application ) ) {
			$this->fail( 'Application was not initialized. Call createClient or createEnvironment' );
			return null;
		}
		/** @var SessionInterface $session */
		$session = $this->app['session'];
		return $session->get( $key, null );
	}

	/**
	 * @todo Change code to work with Symfony DI when switch to Symfony is done
	 * @param string $key
	 * @param mixed $value
	 */
	public function setSessionValue( string $key, $value ): void {
		$this->sessionValues[$key] = $value;
	}

	public function initializeApplicationSessionValues(): void {
		if ( empty( $this->sessionValues ) ) {
			return;
		}

		/** @var SessionInterface $session */
		$session = $this->app['session'];
		foreach ( $this->sessionValues as $key => $value ) {
			$session->set( $key, $value );
		}
	}
}
