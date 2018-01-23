<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\MembershipContext\Domain\Repositories;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class StoreMembershipApplicationException extends \RuntimeException {

	public function __construct( \Exception $previous = null ) {
		parent::__construct( 'Could not store membership application', 0, $previous );
	}

}
