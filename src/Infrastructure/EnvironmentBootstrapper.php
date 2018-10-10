<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\DevelopmentEnvironmentSetup;
use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\EnvironmentSetup;
use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\EnvironmentSetupException;
use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\ProductionEnvironmentSetup;

class EnvironmentBootstrapper {

	private const DEFAULT_ENVIRONMENT_SETUP_MAP = [
		'dev' => DevelopmentEnvironmentSetup::class,
		// User Acceptance Testing should be as close to production as possible
		'uat' => ProductionEnvironmentSetup::class,
		'prod' => ProductionEnvironmentSetup::class
	];

	private $environmentName;

	private $environmentMap;

	public function __construct( string $environmentName, array $environmentMap = [] ) {
		$this->environmentName = $environmentName;
		$this->environmentMap = array_merge( self::DEFAULT_ENVIRONMENT_SETUP_MAP, $environmentMap );
	}

	public function getConfigurationPathsForEnvironment( string $configPath ): array {
		$paths = self::removeNonexistentOptionalPaths( ...[
			$configPath . '/config.dist.json',
			$configPath . '/config.' . $this->environmentName . '.json',
			$configPath . '/config.' . $this->environmentName . '.local.json',
		] );
		self::checkIfPathsExist( ...$paths );
		return $paths;
	}

	private static function removeNonexistentOptionalPaths( string ...$paths ): array {
		if ( !file_exists( $paths[2] ) ) {
			array_splice( $paths, 2 );
		}
		return $paths;
	}

	private static function checkIfPathsExist( string ...$paths ): void {
		array_map(
			function ( $path ) {
				if ( !is_readable( $path ) ) {
					throw new \RuntimeException( 'Configuration file "' . $path . '" not found' );
				}
			},
			$paths
		);
	}

	public function getEnvironmentSetupInstance(): EnvironmentSetup {
		if ( !isset( $this->environmentMap[$this->environmentName] ) ) {
			throw new EnvironmentSetupException( $this->environmentName );
		}
		$class = $this->environmentMap[$this->environmentName];
		return new $class;
	}
}
