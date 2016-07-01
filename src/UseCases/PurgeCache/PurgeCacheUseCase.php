<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\PurgeCache;

use WMDE\Fundraising\Frontend\Infrastructure\CachePurger;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class PurgeCacheUseCase {

	private $expectedSecret;
	private $cachePurger;

	public function __construct( CachePurger $cachePurger, string $secret ) {
		$this->expectedSecret = $secret;
		$this->cachePurger = $cachePurger;
	}

	public function purgeCache( PurgeCacheRequest $request ) {
		if ( !$this->purgeIsAllowed( $request ) ) {
			return;
		}

		$this->cachePurger->purgeCache();
	}

	private function purgeIsAllowed( PurgeCacheRequest $request ): bool {
		return $this->expectedSecret === $request->getSecret();
	}

}
