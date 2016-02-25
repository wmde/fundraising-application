<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain\Repositories;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class GetDonationException extends \RuntimeException {

	public function __construct( \Exception $previous = null ) {
		parent::__construct( 'Could not get donation', 0, $previous );
	}

}
