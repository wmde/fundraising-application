<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\ContentPage;

class PageSelector {

	/**
	 * @param array<string,string> $config pageId => slug
	 */
	public function __construct( private readonly array $config ) {
	}

	public function getPageId( string $slug ): string {
		$pageId = array_search( $slug, $this->config );

		if ( $pageId === false ) {
			throw new PageNotFoundException;
		}

		return $pageId;
	}
}
