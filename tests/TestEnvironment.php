<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests;

use WMDE\Fundraising\Frontend\Factories\EnvironmentDependentConfigReaderFactory;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\EnvironmentBootstrapper;

/**
 * @license GPL-2.0-or-later
 */
class TestEnvironment {

	public static function newInstance( array $config = [] ): self {
		$bootstrapper = new EnvironmentBootstrapper( 'test', [ 'test' => TestEnvironmentSetup::class ] );
		$instance = new self( $config );
		$bootstrapper->getEnvironmentSetupInstance()
			->setEnvironmentDependentInstances( $instance->factory, $config );

		$instance->rebuildDatabaseSchema();

		return $instance;
	}

	private array $config;

	private FunFunFactory $factory;

	private function __construct( array $config ) {
		$configReader = ( new EnvironmentDependentConfigReaderFactory( 'test' ) )->getConfigReader();
		$this->config = \array_replace_recursive( $configReader->getConfig(), $config );
		$this->factory = new FunFunFactory( $this->config );
	}

	private function rebuildDatabaseSchema(): void {
		$schemaCreator = new SchemaCreator( $this->factory->getPlainEntityManager() );

		try {
			$schemaCreator->dropSchema();
		}
		catch ( \Exception $ex ) {
		}

		$schemaCreator->createSchema();
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
