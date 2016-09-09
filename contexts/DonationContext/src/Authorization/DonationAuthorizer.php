<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Authorization;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface DonationAuthorizer {

	/**
	 * Should return false on infrastructure failure.
	 */
	public function userCanModifyDonation( int $donationId ): bool;

	/**
	 * Should return false on infrastructure failure.
	 */
	public function systemCanModifyDonation( int $donationId ): bool;

	/**
	 * Should return false on infrastructure failure.
	 */
	public function canAccessDonation( int $donationId ): bool;

}
