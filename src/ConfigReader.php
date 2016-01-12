<?php

namespace WMDE\Fundraising\Frontend;

use FileFetcher\FileFetcher;
use FileFetcher\FileFetchingException;
use RuntimeException;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ConfigReader {

	private $fileFetcher;
	private $baseConfigFilePath;
	private $instanceConfigFilePath;

	public function __construct( FileFetcher $fileFetcher, string $baseConfigFilePath, string $instanceConfigFilePath = null ) {
		$this->fileFetcher = $fileFetcher;
		$this->baseConfigFilePath = $baseConfigFilePath;
		$this->instanceConfigFilePath = $instanceConfigFilePath;
	}

	/**
	 * @return array
	 * @throws RuntimeException
	 */
	public function getConfig(): array {
		if ( $this->instanceConfigFilePath === null ) {
			return $this->getFileConfig( $this->baseConfigFilePath );
		}

		return array_replace_recursive(
			$this->getFileConfig( $this->baseConfigFilePath ),
			$this->getFileConfig( $this->instanceConfigFilePath )
		);
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

}
