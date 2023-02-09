<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\App\MailTemplateFixtures;

class VariantSettingsGenerator implements TemplateSettingsGenerator {
	private array $variants;

	public function __construct(
		private readonly string $templateName,
		private readonly array $templateData = [],
		TemplateVariant ...$variants
	) {
		$this->variants = $variants;
	}

	/**
	 * @return iterable<TemplateSettings>
	 */
	public function getTemplateSettings(): iterable {
		foreach ( $this->variants as $variant ) {
			$templateData = array_merge( $this->templateData, $variant->additionalTemplateData );
			yield new TemplateSettings( $this->templateName, $this->getId( $variant->name ), $templateData );
		}
	}

	public function getId( string $variantName ): string {
		return basename( $this->templateName, '.txt.twig' ) . '.' . $variantName;
	}

	public function getTemplateName(): string {
		return $this->templateName;
	}

}
