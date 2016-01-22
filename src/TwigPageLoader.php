<?php


namespace WMDE\Fundraising\Frontend;

use Twig_Error_Loader;
use WMDE\Fundraising\Frontend\Presenters\Content\WikiContentProvider;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class TwigPageLoader implements \Twig_LoaderInterface {

	private $contentProvider;
	private $pageCache = [];
	private $errorCache = [];

	public function __construct( WikiContentProvider $contentProvider ) {
		$this->contentProvider = $contentProvider;
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
		// TODO: Check wiki page revisions if page is fresh,
		//   otherwise the Twig page cache has to be cleared manually when the wikipages changes!!!

		// retrieve page to generate loader exception if page does not exist
		$this->retrievePage( $name );
		return true;
	}

	private function retrievePage( string $pageName ): string {
		$title = preg_replace( '/\\.twig$/', '', $pageName );
		if ( isset( $this->pageCache[$title] ) ) {
			return $this->pageCache[$title];
		}
		if ( isset( $this->errorCache[$title] ) ) {
			throw new Twig_Error_Loader( "Wiki page $title not found." );
		}
		$content = $this->contentProvider->getContent( $title );
		if ( $content !== '' ) {
			$this->pageCache[$title] = $content;
			return $content;
		}
		$this->errorCache[$title] = true;
		throw new Twig_Error_Loader( "Wiki page $title not found." );
	}


}