<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\PurgeCache;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class PurgeCacheRequest {

	private $secret;

	public function __construct( string $secret ) {
		$this->secret = $secret;
	}

	public function getSecret(): string {
		return $this->secret;
	}

}
