<?php


namespace WMDE\Fundraising\Frontend;

use Twig_Error_Loader;
use WMDE\Fundraising\Frontend\Domain\PageRetriever;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class TwigPageLoader implements \Twig_LoaderInterface {

	private $pageRetriever;
	private $pageCache = [];
	private $errorCache = [];

	public function __construct( PageRetriever $pageRetriever ) {
		$this->pageRetriever = $pageRetriever;
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
		//   otherwise the Twig page cache has to be cleared manually when the wimkimpages changes!!!

		// retrieve page to generate loader exception if page does not exist
		$this->retrievePage( $name );
		return true;
	}

	private function retrievePage( string $name ): string {
		if ( isset( $this->pageCache[$name] ) ) {
			return $this->pageCache[$name];
		}
		if ( isset( $this->errorCache[$name] ) ) {
			throw new \Twig_Error_Loader( "Wiki page $name not found." );
		}
		$content = $this->pageRetriever->fetchPage( $name );
		if ( $content !== '' ) {
			$this->pageCache[$name] = $content;
			return $content;
		}
		$this->errorCache[$name] = true;
		throw new \Twig_Error_Loader( "Wiki page $name not found." );
	}
}