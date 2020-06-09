<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Translation;

interface TranslatorInterface {

	public function trans( string $messageKey, array $parameters = [] ): string;

}