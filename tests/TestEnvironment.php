<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests;

use FileFetcher\SimpleFileFetcher;

use WMDE\Fundraising\Frontend\Infrastructure\ConfigReader;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\EnvironmentBootstrapper;

/**
 * @licence GNU GPL v2+
 */
class TestEnvironment {

	public static function newInstance( array $config = [] ): self {
		$bootstrapper = new EnvironmentBootstrapper( 'test', [ 'test' => TestEnvironmentSetup::class ] );
		$instance = new self( $config, $bootstrapper );

		$installer = $instance->factory->newInstaller();

		try {
			$installer->uninstall();
		}
		catch ( \Exception $ex ) {
		}

		$installer->install();

		$bootstrapper->getEnvironmentSetupInstance()
			->setEnvironmentDependentInstances( $instance->factory, $config );

		return $instance;
	}

	/**
	 * @var array
	 */
	private $config;

	/**
	 * @var FunFunFactory
	 */
	private $factory;

	private function __construct( array $config, EnvironmentBootstrapper $bootstrapper ) {
		$this->config = array_replace_recursive( $this->getConfigFromFiles( $bootstrapper ), $config );
		$this->factory = new FunFunFactory( $this->config );
	}

	private function getConfigFromFiles( EnvironmentBootstrapper $bootstrapper ): array {
		$configReader = new ConfigReader(
			new SimpleFileFetcher(),
			...$bootstrapper->getConfigurationPathsForEnvironment( __DIR__ . '/../app/config' )
		);

		return $configReader->getConfig();
	}

	public function getFactory(): FunFunFactory {
		return $this->factory;
	}

	public function getConfig(): array {
		return $this->config;
	}

	public static function getTestData( string $fileName ): string {
		return file_get_contents( __DIR__ . '/Data/files/' . $fileName );
	}

	public static function getJsonTestData( string $fileName ): array {
		return json_decode( self::getTestData( $fileName ), true );
	}

}
