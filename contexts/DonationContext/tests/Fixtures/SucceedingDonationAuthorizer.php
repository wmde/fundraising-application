<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures;

use WMDE\Fundraising\Frontend\DonationContext\Authorization\DonationAuthorizer;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SucceedingDonationAuthorizer implements DonationAuthorizer {

	public function userCanModifyDonation( int $donationId ): bool {
		return true;
	}

	public function systemCanModifyDonation( int $donationId ): bool {
		return true;
	}

	public function canAccessDonation( int $donationId ): bool {
		return true;
	}

}