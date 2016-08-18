<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\ApplicationContext\Infrastructure;

use FileFetcher\FileFetcher;
use FileFetcher\FileFetchingException;
use RuntimeException;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ConfigReader {

	private $fileFetcher;

	/**
	 * @var string[]
	 */
	private $configPaths;

	public function __construct( FileFetcher $fileFetcher, string ...$configPaths ) {
		if ( empty( $configPaths ) ) {
			throw new \InvalidArgumentException( 'Need at least one config path' );
		}

		$this->fileFetcher = $fileFetcher;
		$this->configPaths = $configPaths;
	}

	/**
	 * @return array
	 * @throws RuntimeException
	 */
	public function getConfig(): array {
		if ( count( $this->configPaths ) === 1 ) {
			return $this->getFileConfig( reset( $this->configPaths ) );
		}

		$configs = array_map(
			function( string $path ) {
				return $this->getFileConfig( $path );
			},
			$this->configPaths
		);

		return array_replace_recursive( ...$configs );
	}

	public function getConfigObject(): \stdClass {
		return $this->convertConfigArrayToConfigObject( $this->getConfig() );
	}

	private function getFileConfig( string $filePath ): array {
		$config = json_decode( $this->getFileContents( $filePath ), true );

		if ( is_array( $config ) ) {
			return $config;
		}

		throw new RuntimeException( 'No valid config data found in config file at path "' . $filePath . '"' );
	}

	private function getFileContents( string $filePath ): string {
		try {
			return $this->fileFetcher->fetchFile( $filePath );
		}
		catch ( FileFetchingException $ex ) {
			throw new RuntimeException( 'Cannot read config file at path "' . $filePath . '"', 0, $ex );
		}
	}

	private function convertConfigArrayToConfigObject( array $config ): \stdClass {
		// Convert arrays that are supposed to be associative to empty objects,
		// otherwise they will be empty numeric arrays
		// can't use JSON_FORCE_OBJECT
		if ( empty( $config['twig']['loaders']['array'] ) ) {
			$config['twig']['loaders']['array'] = new \stdClass();
		}
		return json_decode( json_encode( $config ), false );
	}

}
