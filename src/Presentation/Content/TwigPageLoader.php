<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Content;

use Twig_Error_Loader;
use WMDE\Fundraising\Frontend\ApplicationContext\Infrastructure\PageRetriever;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class TwigPageLoader implements \Twig_LoaderInterface {

	private $pageRetriever;
	private $pageCache = [];
	private $errorCache = [];
	private $rawPagesList = [];

	public function __construct( PageRetriever $pageRetriever, array $rawPagesList = [] ) {
		$this->pageRetriever = $pageRetriever;
		$this->rawPagesList = $rawPagesList;
	}

	public function getSource( $name ): string {
		return $this->retrievePage( $name );
	}

	public function getCacheKey( $name ): string {
		// retrieve page to generate loader exception if page does not exist
		$this->retrievePage( $name );
		return $name;
	}

	// @codingStandardsIgnoreStart
	public function isFresh( $name, $time ): bool {
		// @codingStandardsIgnoreEnd
		// page is always "fresh" since we have no communication between Wiki and our application
		// Instead, cache is invalidated manually
		return true;
	}

	private function retrievePage( string $pageName ): string {
		$fetchMode = in_array( $pageName, $this->rawPagesList ) ? 'raw' : 'render';
		$title = preg_replace( '/\\.twig$/', '', $pageName );
		if ( isset( $this->pageCache[$title] ) ) {
			return $this->pageCache[$title];
		}
		if ( isset( $this->errorCache[$title] ) ) {
			throw new Twig_Error_Loader( "Wiki page $title not found." );
		}
		$content = $this->pageRetriever->fetchPage( $title, $fetchMode );
		if ( $content !== '' ) {
			$this->pageCache[$title] = $content;
			return $content;
		}
		$this->errorCache[$title] = true;
		throw new Twig_Error_Loader( "Wiki page $title not found." );
	}

}