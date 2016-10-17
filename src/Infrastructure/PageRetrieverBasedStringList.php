<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use WMDE\Fundraising\Frontend\Infrastructure\PageRetriever;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class PageRetrieverBasedStringList implements StringList {

	private $pageRetriever;
	private $pageName;

	public function __construct( PageRetriever $pageRetriever, string $pageName ) {
		$this->pageRetriever = $pageRetriever;
		$this->pageName = $pageName;
	}

	public function toArray(): array {
		if ( $this->pageName === '' ) {
			return [];
		}

		$content = $this->pageRetriever->fetchPage( $this->pageName, PageRetriever::MODE_RAW );

		return array_filter( array_map( 'trim', explode( "\n", $content ) ) );
	}

}
