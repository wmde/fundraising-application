<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App;

use League\JsonGuard\ValidationError;
use League\JsonGuard\Validator;
use WMDE\Fundraising\Frontend\ApplicationContext\Infrastructure\ConfigReader;

require_once __DIR__ . '/../vendor/autoload.php';

define( 'CONFIG_SCHEMA', __DIR__ . '/config/schema.json' );

class ConfigValidator {
	const USAGE_ERROR = 1;
	const VALIDATION_ERROR = 2;

	private $schema;
	private $config;

	public function validate( array $configFiles, string $schema ) {
		$this->loadConfigObjectFromFiles( $configFiles );
		$this->loadSchema( $schema );

		$validator = new Validator(
			$this->config,
			$this->schema
		);

		if ( $validator->passes() ) {
			exit( 0 );
		}

		$this->renderErrors( $validator->errors() );

		exit( self::VALIDATION_ERROR );

	}

	private function loadConfigObjectFromFiles( $configFiles ) {
		if ( count( $configFiles ) === 0 ) {
			$this->exitWithError( "You must specify at least one config file.\n" );
		}

		$configReader = new ConfigReader(
			new \FileFetcher\SimpleFileFetcher(),
			...$configFiles
		);

		try {
			$this->config = $this->convertConfigArrayToConfigObject( $configReader->getConfig() );
		} catch ( \RuntimeException $e ) {
			$this->exitWithError( $e->getMessage() );
		}
	}

	private function convertConfigArrayToConfigObject( array $config ): \stdClass {
		// empty array is converted to array not to object
		if ( empty( $config['twig']['loaders']['array'] ) ) {
			$config['twig']['loaders']['array'] = new \stdClass();
		}
		return json_decode( json_encode( $config ), false );
	}

	private function loadSchema( $schema ) {
		$fileFetcher = new \FileFetcher\SimpleFileFetcher();
		try {
			$schemaString = $fileFetcher->fetchFile( $schema );
		} catch ( \FileFetcher\FileFetchingException $e ) {
			$this->exitWithError( $e->getMessage() );
		}
		$schema = json_decode( $schemaString );
		if ( $schema === null ) {
			$this->exitWithError( 'Error parsing the schema file: ' .json_last_error_msg() );
		}
		$this->schema = $schema;
	}

	private function exitWithError( $message, $returnCode = self::USAGE_ERROR ) {
		echo "$message\n";
		exit( $returnCode );
	}

	/**
	 * @param ValidationError[] $errors
	 */
	private function renderErrors( array $errors ) {
		foreach ( $errors as $error ) {
			if ( $error->getCode() === \League\JsonGuard\ErrorCode::MISSING_REQUIRED ) {
				$keys = array_keys( get_object_vars( $error->getValue() ) );
				$missingKeys = array_diff( $error->getConstraints()['required'], $keys );
				printf( "Error in '%s': Missing the following keys: %s\n", $error->getPointer(), implode( ',', $missingKeys ) );
				continue;
			}
			if ( $error->getCode() === \League\JsonGuard\ErrorCode::NOT_ALLOWED_PROPERTY ) {
				// TODO Use pointer to navigate to schema properties
				if ( $error->getPointer() === '' ) {
					$keys = array_keys( get_object_vars( $error->getValue() ) );
					$allowedProperties = array_keys( get_object_vars( $this->schema->properties ) );
					$additionalKeys = array_diff( $keys, $allowedProperties );
					printf( "Error in '%s': Unspecified keys: %s\n", $error->getPointer(), implode( ',', $additionalKeys ) );
				} else {
					printf( "Error in '%s', please check the object keys.\n", $error->getPointer() );
				}

				continue;
			}
			printf( "Error in '%s': %s\n", $error->getPointer(), $error->getMessage() );
		}
	}
}

( new ConfigValidator() )->validate( array_slice( $argv, 1 ), CONFIG_SCHEMA );
