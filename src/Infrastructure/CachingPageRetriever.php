<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use Doctrine\Common\Cache\Cache;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CachingPageRetriever implements PageRetriever {

	private $pageRetriever;
	private $cache;

	public function __construct( PageRetriever $pageRetriever, Cache $cache ) {
		$this->pageRetriever = $pageRetriever;
		$this->cache = $cache;
	}

	public function fetchPage( string $pageName, string $fetchMode ): string {
		if ( $this->cache->contains( $pageName ) ) {
			return $this->cache->fetch( $pageName );
		}

		$content = $this->pageRetriever->fetchPage( $pageName, $fetchMode );

		$this->cache->save( $pageName, $content );

		return $content;
	}

}
