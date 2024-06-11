<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Translation;

interface TranslatorInterface {

	/**
	 * @param string $messageKey
	 * @param array<string, scalar> $parameters
	 */
	public function trans( string $messageKey, array $parameters = [] ): string;

}
