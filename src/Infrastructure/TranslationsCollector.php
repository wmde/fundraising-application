<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use FileFetcher\FileFetcher;
use RuntimeException;

class TranslationsCollector {

	private array $translationFiles = [];

	public function __construct( private readonly FileFetcher $fileFetcher ) {
	}

	public function addTranslationFile( string $transFile ): void {
		$this->translationFiles[] = $transFile;
	}

	public function collectTranslations(): array {
		$result = [];

		foreach ( $this->translationFiles as $transFile ) {
			$jsonFileContent = json_decode( $this->fileFetcher->fetchFile( $transFile ), true );
			if ( !is_array( $jsonFileContent ) ) {
				throw new RuntimeException( 'The file must contain a key,value data structure.' );
			}
			$result = array_merge( $result, $jsonFileContent );
		}

		return $result;
	}
}
