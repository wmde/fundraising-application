<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use FileFetcher\ErrorLoggingFileFetcher;
use FileFetcher\FileFetchingException;
use WMDE\FunValidators\StringList;

class WordListFileReader implements StringList {

	public function __construct(
		private readonly ErrorLoggingFileFetcher $fileFetcher,
		private readonly string $fileName
	) {
	}

	/**
	 * @return string[]
	 */
	public function toArray(): array {
		if ( $this->fileName === '' ) {
			return [];
		}

		try {
			$content = $this->fileFetcher->fetchFile( $this->fileName );
		} catch ( FileFetchingException $e ) {
			// The error will be logged and we don't want to bring down the application with missing files
			$content = '';
		}

		return array_values( array_filter( array_map( 'trim', explode( "\n", $content ) ) ) );
	}

}
