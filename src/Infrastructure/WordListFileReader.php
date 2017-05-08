<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use FileFetcher\FileFetchingException;

class WordListFileReader implements StringList {

	private $fileFetcher;
	private $fileName;

	public function __construct( ErrorLoggingFileFetcher $fileFetcher, string $fileName ) {
		$this->fileFetcher = $fileFetcher;
		$this->fileName = $fileName;
	}

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
