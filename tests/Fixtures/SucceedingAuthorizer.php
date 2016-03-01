<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Infrastructure\AuthorizationService;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SucceedingAuthorizer implements AuthorizationService {

	public function canModifyDonation( int $donationId ): bool {
		return true;
	}

}