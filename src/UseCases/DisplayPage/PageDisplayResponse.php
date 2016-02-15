<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\UseCases\DisplayPage;

use WMDE\Fundraising\Frontend\FreezableValueObject;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class PageDisplayResponse {
	use FreezableValueObject;

	private $mainContentTemplate;
	private $templateExists;

	public function getMainContentTemplate(): string {
		return $this->mainContentTemplate;
	}

	public function setMainContentTemplate( string $mainContentTemplate ): PageDisplayResponse {
		$this->assertIsWritable();
		$this->mainContentTemplate = $mainContentTemplate;
		return $this;
	}

	public function getTemplateExists(): bool {
		return $this->templateExists;
	}

	public function setTemplateExists( bool $templateExists ): PageDisplayResponse {
		$this->assertIsWritable();
		$this->templateExists = $templateExists;
		return $this;
	}


}

