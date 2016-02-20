<?php

namespace WMDE\Fundraising\Frontend\Domain\Repositories;

use WMDE\Fundraising\Frontend\Domain\Model\Donation;

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
