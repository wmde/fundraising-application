<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Infrastructure\Translation\TranslatorInterface;

class FakeTranslator implements TranslatorInterface {

	public function trans( string $messageKey, array $parameters = [] ): string {
		return $messageKey;
	}

}
