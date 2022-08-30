<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Infrastructure\Translation\TranslatorInterface;

class InMemoryTranslator implements TranslatorInterface {

	/**
	 * @param array<string,string> $messages
	 */
	public function __construct( private readonly array $messages ) {
	}

	public function trans( string $messageKey, array $parameters = [] ): string {
		if ( !isset( $this->messages[$messageKey] ) ) {
			throw new \InvalidArgumentException( "Unknown translation key: $messageKey" );
		}
		return str_replace( array_keys( $parameters ), array_values( $parameters ), $this->messages[$messageKey] );
	}

}
