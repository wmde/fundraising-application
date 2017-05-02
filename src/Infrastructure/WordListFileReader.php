<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use FileFetcher\FileFetcher;

class WordListFileReader implements StringList {

	private $fileFetcher;
	private $fileName;

	public function __construct( FileFetcher $fileFetcher, string $fileName ) {
		$this->fileFetcher = $fileFetcher;
		$this->fileName = $fileName;
	}

	public function toArray(): array {
		if ( $this->fileName === '' ) {
			return [];
		}

		$content = $this->fileFetcher->fetchFile( $this->fileName );

		return array_values( array_filter( array_map( 'trim', explode( "\n", $content ) ) ) );

	}

}
