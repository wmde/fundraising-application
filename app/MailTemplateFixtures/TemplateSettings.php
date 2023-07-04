<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\App\MailTemplateFixtures;

/**
 * A value object to render a mail template
 */
class TemplateSettings {
	/**
	 * @param string $templateName Name of the mail template. Should end in ".txt.twig"
	 * @param string $id Unique ID for rendered output. Contains name of the template, might contain other information to make the ID unique (if code renders the template multiple times).
	 * @param array<string,mixed> $templateData Data passed to the template engine
	 */
	public function __construct(
		public readonly string $templateName,
		public readonly string $id,
		public readonly array $templateData = [] ) {
	}
}
