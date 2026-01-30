<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\BrowserKitAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\EnvironmentBootstrapper;
use WMDE\Fundraising\Frontend\Tests\PrepareSessionValuesTrait;
use WMDE\Fundraising\Frontend\Tests\RebuildDatabaseSchemaTrait;
use WMDE\Fundraising\Frontend\Tests\TestEnvironmentBootstrapper;

abstract class WebRouteTestCase extends KernelTestCase {

	use BrowserKitAssertionsTrait;
	use RebuildDatabaseSchemaTrait;
	use PrepareSessionValuesTrait;

	protected const DISABLE_DEBUG = false;
	protected const ENABLE_DEBUG = true;
	protected static array $applicationConfiguration = [];

	/**
	 * phpcs:ignore MediaWiki.Commenting.PropertyDocumentation.NotPunctuationVarType
	 * @var ?callable(FunFunFactory): void $environmentModification
	 */
	protected static $environmentModification = null;
	/**
	 * @var array<string, scalar>
	 */
	protected array $sessionValues = [];

	protected static bool $factoryInitialized = false;

	protected function tearDown(): void {
		parent::tearDown();
		static::$applicationConfiguration = [];
		static::$environmentModification = null;
		$this->sessionValues = [];
		static::$factoryInitialized = false;
	}

	/**
	 * Initializes a new test environment and returns a HttpKernel client to
	 * make requests to the application.
	 */
	protected static function createClient(): KernelBrowser {
		if ( static::$booted ) {
			throw new \LogicException( sprintf( 'Booting the kernel before calling "%s()" is not supported, the kernel should only be booted once.', __METHOD__ ) );
		}

		$kernel = static::bootKernel();

		try {
			/** @var KernelBrowser $client */
			$client = $kernel->getContainer()->get( 'test.client' );
		} catch ( ServiceNotFoundException $e ) {
			if ( class_exists( KernelBrowser::class ) ) {
				throw new \LogicException( 'You cannot create the client used in functional tests if the "framework.test" config is not set to true.' );
			}
			throw new \LogicException( 'You cannot create the client used in functional tests if the BrowserKit component is not available. Try running "composer require symfony/browser-kit".' );
		}

		// PHPStan workaround for missing Symfony typing (`BrowserKitAssertionsTrait::getClient()` returns `AbstractBrowser`)
		/** @var KernelBrowser $client */
		$client = self::getClient( $client );
		return $client;
	}

	/**
	 * Run test code that needs access to the central FunFunFactory.
	 *
	 * This is a "legacy" function for test code that needs access to FunFunFactory
	 * (which was not accessible before). For new test code you should use "getFactory" instead.
	 *
	 * @deprecated Use getClient instead
	 * @param callable(KernelBrowser, FunFunFactory): void $onEnvironmentCreated
	 */
	protected function createEnvironment( callable $onEnvironmentCreated ): void {
		$client = static::createClient();
		// Don't throw away the environment between http requests, otherwise the in-memory SQLite database would be gone
		$client->disableReboot();
		call_user_func(
			$onEnvironmentCreated,
			$client,
			static::getFactory()
		);
	}

	/**
	 * Change application configuration values.
	 *
	 * Each value provided will be merged into the application configuration,
	 * overriding the values from the configuration file (app/config/config.test.json).
	 */
	protected static function modifyConfiguration( array $config ): void {
		static::$applicationConfiguration = $config;
	}

	/**
	 * Run setup code that needs access to the central FunFunFactory.
	 *
	 * This is a "legacy" function for test code that needs access to FunFunFactory
	 * (which was not accessible before). For new test code you should use "getFactory" instead.
	 *
	 * @deprecated use getFactory instead
	 * @param callable(FunFunFactory): void $doModify
	 */
	protected static function modifyEnvironment( callable $doModify ): void {
		static::$environmentModification = $doModify;
	}

	protected static function getFactory(): FunFunFactory {
		if ( !static::$booted ) {
			throw new \LogicException( sprintf( 'Currently, the kernel must be booted before calling "%s()". Try calling "createClient" or "bootKernel"', __METHOD__ ) );
		}
		/** @var FunFunFactory $factory */
		$factory = static::getContainer()->get( FunFunFactory::class );
		return $factory;
	}

	/**
	 * Applies configuration changes, environment modifications and recreates the database schema
	 * before returning the booted kernel.
	 *
	 * This method is needed as long as the FunFunFactory creates most of our services
	 *
	 * @override
	 * @param string[] $options
	 */
	protected static function bootKernel( array $options = [] ): KernelInterface {
		// `bootKernel` initializes all Symfony dependencies. If any of those depends on the FunFunFactory,
		// overriding the configuration will break because by the time we call `static::getFactory`,
		// it will already be initialized (with the unmodified configuration).
		// A stopgap solution is to move those services into `services_prod.yml`.
		// A better solution would be to make those services independent of FunFunFactory.
		$kernel = parent::bootKernel( $options );

		self::modifyBootstrapperConfiguration();
		$factory = static::getFactory();
		static::rebuildDatabaseSchema( $factory );

		if ( is_callable( static::$environmentModification ) ) {
			call_user_func( static::$environmentModification, $factory );
		}

		return $kernel;
	}

	private static function modifyBootstrapperConfiguration(): void {
		if ( !static::$applicationConfiguration ) {
			return;
		}

		$bootstrapper = static::getContainer()->get( EnvironmentBootstrapper::class );
		if ( !( $bootstrapper instanceof TestEnvironmentBootstrapper ) ) {
			throw new \LogicException( 'When overriding configuration, the environment must use TestEnvironmentBootstrapper' );
		}

		$bootstrapper->overrideConfiguration( static::$applicationConfiguration );
	}

	protected function assert404( Response $response ): void {
		$this->assertSame( 404, $response->getStatusCode() );
	}

	/**
	 * @param array<int|string, array<int|string,int|float|string>|string|mixed> $expected
	 * @param Response $response
	 */
	protected function assertJsonResponse( array $expected, Response $response ): void {
		$this->assertSame(
			json_encode( $expected, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ),
			$response->getContent()
		);
	}

	/**
	 * @param array<int|string, string|array<string, int|float|string>> $expected
	 * @param Response $response
	 */
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

	protected function assertAPIErrorJsonResponse( Response $response, string $expectedMessage ): void {
		$responseData = $this->getJsonFromResponse( $response );
		$this->assertArrayHasKey( 'ERR', $responseData );
		$this->assertSame( $expectedMessage, $responseData['ERR'] );
	}

	protected function getJsonFromResponse( Response $response ): array {
		$this->assertJson( $response->getContent(), 'response is json' );
		$result = json_decode( $response->getContent(), true );
		$this->assertIsArray( $result );
		return $result;
	}

	protected function assertSuccessJsonResponse( Response $response ): void {
		$responseData = $this->getJsonFromResponse( $response );
		$this->assertArrayHasKey( 'status', $responseData );
		$this->assertSame( 'OK', $responseData['status'] );
		$this->assertArrayHasKey( 'message', $responseData );
	}

	/**
	 * @param array<string, string|string[]> $expected
	 * @param AbstractBrowser<Request, Response> $client
	 */
	protected function assertInitialFormValues( array $expected, AbstractBrowser $client ): void {
		$initialFormValues = $client->getCrawler()->filter( 'script[data-initial-form-values]' );
		$this->assertGreaterThan(
			0,
			$initialFormValues->count(),
			'HTML should contain initial values'
		);
		$json = $initialFormValues->attr( 'data-initial-form-values' ) ?? '';
		$data = json_decode( $json, true );
		$this->assertEquals( $expected, $data );
	}

}
