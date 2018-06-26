<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Cli\ApplicationConfigValidation;

use FileFetcher\FileFetcher;
use FileFetcher\FileFetchingException;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class SchemaLoader {

	private $fileFetcher;

	public function __construct( FileFetcher $fileFetcher ) {
		$this->fileFetcher = $fileFetcher;
	}

	public function loadSchema( string $schema ): \stdClass {
		try {
			$schemaString = $this->fileFetcher->fetchFile( $schema );
		} catch ( FileFetchingException $e ) {
			throw new ConfigValidationException( $e->getMessage(), $e->getCode(), $e );
		}
		$schema = json_decode( $schemaString );
		if ( $schema === null ) {
			throw new ConfigValidationException( 'Error parsing the schema file: ' .json_last_error_msg() );
		}
		if ( !is_object( $schema ) ) {
			throw new ConfigValidationException( 'Schema must be a JSON object.' );
		}
		return $schema;
	}
}