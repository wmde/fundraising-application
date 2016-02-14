<?php

namespace WMDE\Fundraising\Frontend\Domain;

use WMDE\Fundraising\Entities\Donation;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
interface DonationRepository {

	/**
	 * TODO: add exception
	 *
	 * @param Donation $donation
	 */
	public function storeDonation( Donation $donation );

}
