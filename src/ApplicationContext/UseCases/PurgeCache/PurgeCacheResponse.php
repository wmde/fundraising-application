<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\ApplicationContext\UseCases\PurgeCache;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class PurgeCacheResponse {

	const SUCCESS = 0;
	const ERROR = 1;
	const ACCESS_DENIED = 2;

	private $state;

	public function __construct( int $state ) {
		$this->state = $state;
	}

	public function getState(): int {
		return $this->state;
	}

}
