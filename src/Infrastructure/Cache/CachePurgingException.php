<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Cache;

/**
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CachePurgingException extends \RuntimeException {

	public function __construct( string $message, \Exception $previous = null ) {
		parent::__construct( $message, 0, $previous );
	}

}
