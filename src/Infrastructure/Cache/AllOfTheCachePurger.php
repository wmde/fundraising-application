<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Cache;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FlushableCache;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AllOfTheCachePurger implements CachePurger {

	private $twigEnvironment;
	private $pageCache;

	public function __construct( \Twig_Environment $twigEnvironment, Cache $pageCache ) {
		$this->twigEnvironment = $twigEnvironment;
		$this->pageCache = $pageCache;
	}

	/**
	 * @throws CachePurgingException
	 */
	public function purgeCache() {
		$this->twigEnvironment->clearCacheFiles();
		$this->twigEnvironment->clearTemplateCache();

		if ( $this->pageCache instanceof FlushableCache ) {
			$this->pageCache->flushAll();
		}
	}

}
