<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Factories;

use FileFetcher\SimpleFileFetcher;
use WMDE\Fundraising\Frontend\Infrastructure\ConfigReader;

class EnvironmentDependentConfigReaderFactory {
	private string $environmentName;

	public function __construct( string $environmentName ) {
		$this->environmentName = $environmentName;
	}

	public function getConfigReader(): ConfigReader {
		return new ConfigReader(
			new SimpleFileFetcher(),
			...$this->getConfigurationPathsForEnvironment( __DIR__ . '/../../app/config' )
		);
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
			static function ( $path ) {
				if ( !is_readable( $path ) ) {
					throw new \RuntimeException( 'Configuration file "' . $path . '" not found' );
				}
			},
			$paths
		);
	}
}
