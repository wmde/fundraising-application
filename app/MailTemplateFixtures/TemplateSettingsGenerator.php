<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\App\MailTemplateFixtures;

/**
 * A template settings provider generates one or more template settings that code can use to render mail templates.
 */
interface TemplateSettingsGenerator {
	/**
	 * @return iterable<TemplateSettings>
	 */
	public function getTemplateSettings(): iterable;

	public function getTemplateName(): string;
}
