<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Infrastructure;

use FileFetcher\FileFetcher;
use FileFetcher\FileFetchingException;
use RuntimeException;

class TranslationsCollector {

	private $fileFetcher;
	private $translationFiles = [];

	public function __construct( FileFetcher $fileFetcher ) {
		$this->fileFetcher = $fileFetcher;
	}

	public function addTranslationFile( string $transFile ) {

		$this->translationFiles[]= $transFile;
	}

	/**
	 * @throws FileFetchingException
	 */
	public function collectTranslations(): array {

		$result = [];

		foreach ( $this->translationFiles as $transFile) {
			$jsonFileContent = json_decode( $this->fileFetcher->fetchFile( $transFile ), true );
			if (! is_array( $jsonFileContent ) ) {
				throw new RuntimeException( 'The file must contain a key,value data structure.' );
			}
			$result = array_merge( $result, $jsonFileContent );
		}



		return $result;
	}
}