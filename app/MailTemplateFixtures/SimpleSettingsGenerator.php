<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\App\MailTemplateFixtures;

class SimpleSettingsGenerator implements TemplateSettingsGenerator {
	public function __construct(
		private readonly string $templateName,
		private readonly array $templateData = []
	) {
	}

	/**
	 * @return iterable<TemplateSettings>
	 */
	public function getTemplateSettings(): iterable {
		yield new TemplateSettings( $this->templateName, $this->getId(), $this->templateData );
	}

	private function getId(): string {
		return basename( $this->templateName, '.txt.twig' );
	}

	public function getTemplateName(): string {
		return $this->templateName;
	}
}
