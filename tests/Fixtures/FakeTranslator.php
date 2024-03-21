<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Infrastructure\Translation\TranslatorInterface;

class FakeTranslator implements TranslatorInterface {

	/**
	 * @param string $messageWrapper A string to wrap the untranslated translation keys with to verify they were handled
	 *                               by the translator. Must contain exactly one %s placeholder to indicate message key.
	 */
	public function __construct( private readonly string $messageWrapper = "%s" ) {
	}

	public function trans( string $messageKey, array $parameters = [] ): string {
		return sprintf( $this->messageWrapper, $messageKey );
	}

}
