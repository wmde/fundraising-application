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

	private $mainContent;

	public function getMainContent(): string {
		return $this->mainContent;
	}

	public function setMainContent( string $mainContent ) {
		$this->assertIsWritable();
		$this->mainContent = $mainContent;
	}
}

