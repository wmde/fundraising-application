<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Cache;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\FlushableCache;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AllOfTheCachePurger implements CachePurger {

	private $twigEnvironment;
	private $rawPageCache;
	private $renderedPageCache;
	private $campaignCache;

	public function __construct( \Twig_Environment $twigEnvironment, Cache $rawPageCache, Cache $renderedPageCache,
								 CacheProvider $campaignCache ) {
		$this->twigEnvironment = $twigEnvironment;
		$this->rawPageCache = $rawPageCache;
		$this->renderedPageCache = $renderedPageCache;
		$this->campaignCache = $campaignCache;
	}

	/**
	 * @throws CachePurgingException
	 */
	public function purgeCache(): void {
		$this->twigEnvironment->clearCacheFiles();
		$this->twigEnvironment->clearTemplateCache();

		if ( $this->rawPageCache instanceof FlushableCache ) {
			$this->rawPageCache->flushAll();
		}

		if ( $this->renderedPageCache instanceof FlushableCache ) {
			$this->renderedPageCache->flushAll();
		}

		if ( $this->campaignCache instanceof FlushableCache ) {
			$this->campaignCache->flushAll();
		}
	}

}
