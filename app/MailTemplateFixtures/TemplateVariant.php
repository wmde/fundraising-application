<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\App\MailTemplateFixtures;

class TemplateVariant {
	public function __construct( public readonly string $name, public readonly array $additionalTemplateData ) {
	}
}
