<?php

namespace WMDE\Fundraising\Frontend\UseCases\DisplayPage;

use WMDE\Fundraising\Frontend\FreezableValueObject;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class PageDisplayResponse {
	use FreezableValueObject;

	private $mainContent;
	private $headerContent;
	private $footerContent;

	public function getMainContent(): string {
		return $this->mainContent;
	}

	public function setMainContent( string $mainContent ) {
		$this->assertIsWritable();
		$this->mainContent = $mainContent;
	}

	public function getHeaderContent(): string {
		return $this->headerContent;
	}

	public function setHeaderContent( string $headerContent ) {
		$this->assertIsWritable();
		$this->headerContent = $headerContent;
	}

	public function getFooterContent(): string {
		return $this->footerContent;
	}

	public function setFooterContent( string $footerContent ) {
		$this->assertIsWritable();
		$this->footerContent = $footerContent;
	}

}

