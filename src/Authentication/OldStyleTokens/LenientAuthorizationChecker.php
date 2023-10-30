<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Authentication\OldStyleTokens;

use WMDE\Fundraising\DonationContext\Infrastructure\DonationAuthorizationChecker;

/**
 * This class allows the system to modify donations without checking the donation token.
 * This is for cases where we no longer have the token, but have other means of checking
 * whether an incoming request is genuine.
 * Example: PayPal IPN, where we send the request back to PayPal for verification.
 */
class LenientAuthorizationChecker implements DonationAuthorizationChecker {

	public function userCanModifyDonation( int $donationId ): bool {
		return false;
	}

	public function systemCanModifyDonation( int $donationId ): bool {
		return true;
	}

	public function canAccessDonation( int $donationId ): bool {
		return true;
	}
}
