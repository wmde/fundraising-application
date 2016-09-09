<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Authorization;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DonationTokenFetchingException extends \RuntimeException {

	public function __construct( string $message, \Exception $previous = null ) {
		parent::__construct( $message, 0, $previous );
	}

}
