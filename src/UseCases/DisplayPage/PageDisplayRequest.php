<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\UseCases\DisplayPage;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class PageDisplayRequest {

	private $pageName;

	public function __construct( string $pageName ) {
		$this->pageName = $pageName;
	}

	public function getPageName(): string {
		return $this->pageName;
	}

}
