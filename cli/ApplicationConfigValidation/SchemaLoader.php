<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Cli\ApplicationConfigValidation;

use FileFetcher\FileFetcher;
use FileFetcher\FileFetchingException;

class SchemaLoader {

	public function __construct( private readonly FileFetcher $fileFetcher ) {
	}

	public function loadSchema( string $schema ): object {
		try {
			$schemaString = $this->fileFetcher->fetchFile( $schema );
		} catch ( FileFetchingException $e ) {
			throw new ConfigValidationException( $e->getMessage(), $e->getCode(), $e );
		}
		$schema = json_decode( $schemaString );
		if ( $schema === null ) {
			throw new ConfigValidationException( 'Error parsing the schema file: ' . json_last_error_msg() );
		}
		if ( !is_object( $schema ) ) {
			throw new ConfigValidationException( 'Schema must be a JSON object.' );
		}
		return $schema;
	}
}
