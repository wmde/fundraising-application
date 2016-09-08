<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonatingContext\Tests\Fixtures;

use WMDE\Fundraising\Frontend\DonatingContext\Authorization\DonationAuthorizer;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class FailingDonationAuthorizer implements DonationAuthorizer {

	public function userCanModifyDonation( int $donationId ): bool {
		return false;
	}

	public function systemCanModifyDonation( int $donationId ): bool {
		return false;
	}

	public function canAccessDonation( int $donationId ): bool {
		return false;
	}

}