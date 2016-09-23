<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Cache;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AuthorizedCachePurger {

	const RESULT_SUCCESS = 0;
	const RESULT_ERROR = 1;
	const RESULT_ACCESS_DENIED = 2;

	private $expectedSecret;
	private $cachePurger;

	public function __construct( CachePurger $cachePurger, string $secret ) {
		$this->expectedSecret = $secret;
		$this->cachePurger = $cachePurger;
	}

	public function purgeCache( string $authorizationSecret ): int {
		if ( !$this->purgeIsAllowed( $authorizationSecret ) ) {
			return self::RESULT_ACCESS_DENIED;
		}

		try {
			$this->cachePurger->purgeCache();
		}
		catch ( CachePurgingException $ex ) {
			return self::RESULT_ERROR;
		}

		return self::RESULT_SUCCESS;
	}

	private function purgeIsAllowed( string $secret ): bool {
		return $this->expectedSecret === $secret;
	}

}
