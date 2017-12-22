<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\Domain\Repositories;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApplicationPurgedException extends GetMembershipApplicationException {

	public function __construct() {
		parent::__construct( 'Tried to access a purged Application' );
	}

}
