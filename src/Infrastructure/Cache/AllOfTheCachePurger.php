<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Cache;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\FlushableCache;

/**
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AllOfTheCachePurger implements CachePurger {

	private Cache $rawPageCache;
	private Cache $renderedPageCache;
	private Cache $campaignCache;

	public function __construct( Cache $rawPageCache, Cache $renderedPageCache, CacheProvider $campaignCache ) {
		$this->rawPageCache = $rawPageCache;
		$this->renderedPageCache = $renderedPageCache;
		$this->campaignCache = $campaignCache;
	}

	/**
	 * @throws CachePurgingException
	 */
	public function purgeCache(): void {
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
