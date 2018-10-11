<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Infrastructure;

use FileFetcher\FileFetcher;

class JsonStringReader {

	private $file;
	private $fileFetcher;
	private $json;

	public function __construct( string $file , FileFetcher $fileFetcher ) {
		$this->file = $file;
		$this->fileFetcher = $fileFetcher;
	}

	private function getJsonFile(): string {
		return $this->fileFetcher->fetchFile( $this->file );
	}

	private function isJsonEmpty(): bool {
		return $this->json === '';
	}

	private function isJsonValid(): bool {
		json_decode( $this->json );
		return json_last_error() === JSON_ERROR_NONE;
	}

	public function readAndValidateJson(): string {
		$this->json = $this->getJsonFile();
		if ( $this->isJsonEmpty() || !$this->isJsonValid() ) {
			throw new \RuntimeException( 'error_invalid_json' );
		}
		return $this->json;
	}
}