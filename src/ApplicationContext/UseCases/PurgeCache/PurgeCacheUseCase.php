<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\ApplicationContext\UseCases\PurgeCache;

use WMDE\Fundraising\Frontend\ApplicationContext\Infrastructure\CachePurger;
use WMDE\Fundraising\Frontend\Infrastructure\CachePurgingException;

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

	public function purgeCache( PurgeCacheRequest $request ): PurgeCacheResponse {
		if ( !$this->purgeIsAllowed( $request ) ) {
			return new PurgeCacheResponse( PurgeCacheResponse::ACCESS_DENIED );
		}

		try {
			$this->cachePurger->purgeCache();
		}
		catch ( CachePurgingException $ex ) {
			return new PurgeCacheResponse( PurgeCacheResponse::ERROR );
		}

		return new PurgeCacheResponse( PurgeCacheResponse::SUCCESS );
	}

	private function purgeIsAllowed( PurgeCacheRequest $request ): bool {
		return $this->expectedSecret === $request->getSecret();
	}

}
