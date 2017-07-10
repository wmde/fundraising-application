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
	private $rawPageCache;
	private $renderedPageCache;

	public function __construct( \Twig_Environment $twigEnvironment, Cache $rawPageCache, Cache $renderedPageCache ) {
		$this->twigEnvironment = $twigEnvironment;
		$this->rawPageCache = $rawPageCache;
		$this->renderedPageCache = $renderedPageCache;
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
	}

}
