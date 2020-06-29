<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Translation;

use FileFetcher\FileFetcher;

class JsonTranslator implements TranslatorInterface {

	private FileFetcher $fileFetcher;
	private array $messages;

	public function __construct( FileFetcher $fileFetcher ) {
		$this->fileFetcher = $fileFetcher;
		$this->messages = [];
	}

	public function addFile( string $fileName ): self {
		$contents = $this->fileFetcher->fetchFile( $fileName );
		$this->messages = array_merge( $this->messages, json_decode( $contents, true ) );
		return $this;
	}

	public function trans( string $messageKey, array $parameters = [] ): string {
		if ( !isset( $this->messages[$messageKey] ) ) {
			throw new \InvalidArgumentException( "Unknown translation key: $messageKey" );
		}
		return str_replace( array_keys( $parameters ), array_values( $parameters ), $this->messages[$messageKey] );
	}

}
