<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Infrastructure\DonationAuthorizer;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SucceedingDonationAuthorizer implements DonationAuthorizer {

	public function canModifyDonation( int $donationId ): bool {
		return true;
	}

	public function canAccessDonation( int $donationId ): bool {
		return true;
	}

}