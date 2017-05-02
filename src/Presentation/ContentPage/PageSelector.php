<?php

declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Presentation\ContentPage;

class PageSelector {

	private $config;

	public function __construct( array $config ) {
		$this->config = $config;
	}

	/**
	 * @throws PageNotFoundException
	 */
	public function getPageId( string $slug ): string {
		$pageId = array_search( $slug, $this->config );

		if ( $pageId === false ) {
			throw new PageNotFoundException;
		}

		return $pageId;
	}
}
