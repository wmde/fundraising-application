<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\BrowserKitAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\EnvironmentBootstrapper;
use WMDE\Fundraising\Frontend\Tests\PrepareSessionValuesTrait;
use WMDE\Fundraising\Frontend\Tests\RebuildDatabaseSchemaTrait;
use WMDE\Fundraising\Frontend\Tests\TestEnvironmentBootstrapper;

/**
 * @license GPL-2.0-or-later
 */
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
	 *
	 * @return AbstractBrowser
	 */
	protected static function createClient(): AbstractBrowser {
		if ( static::$booted ) {
			throw new \LogicException( sprintf( 'Booting the kernel before calling "%s()" is not supported, the kernel should only be booted once.', __METHOD__ ) );
		}

		$kernel = static::bootKernel();

		try {
			$client = $kernel->getContainer()->get( 'test.client' );
		} catch ( ServiceNotFoundException $e ) {
			if ( class_exists( KernelBrowser::class ) ) {
				throw new \LogicException( 'You cannot create the client used in functional tests if the "framework.test" config is not set to true.' );
			}
			throw new \LogicException( 'You cannot create the client used in functional tests if the BrowserKit component is not available. Try running "composer require symfony/browser-kit".' );
		}

		return self::getClient( $client );
	}

	/**
	 * Run test code that needs access to the central FunFunFactory.
	 *
	 * This is a "legacy" function for test code that needs access to FunFunFactory
	 * (which was not accessible before). For new test code you should use "getFactory" instead.
	 *
	 * @param callable(KernelBrowser, FunFunFactory): void $onEnvironmentCreated
	 */
	protected function createEnvironment( callable $onEnvironmentCreated ): void {
		$client = static::createClient();
		// Don't throw away the environment between http requests, otherwise the in-memory SQLite database would be gone
		if ( $client instanceof KernelBrowser ) {
			$client->disableReboot();
		}
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
	 *
	 * @param array $config
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
	 * @param callable(FunFunFactory): void $doModify
	 */
	protected static function modifyEnvironment( callable $doModify ): void {
		static::$environmentModification = $doModify;
	}

	protected static function getFactory(): FunFunFactory {
		if ( !static::$booted ) {
			throw new \LogicException( sprintf( 'Currently, the kernel must be booted before calling "%s()". Try calling "createClient" or "bootKernel"', __METHOD__ ) );
		}
		return static::getContainer()->get( FunFunFactory::class );
	}

	/**
	 * Applies configuration changes, environment modifications and recreates the database schema
	 * before returning the booted kernel.
	 *
	 * This method is needed as long as the FunFunFactory creates most of our services
	 *
	 * @override
	 * @param array $options
	 * @return KernelInterface
	 */
	protected static function bootKernel( array $options = [] ): KernelInterface {
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

	protected function assertInitialFormValues( array $expected, AbstractBrowser $client ): void {
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
