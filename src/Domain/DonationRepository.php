<?php

namespace WMDE\Fundraising\Frontend\Domain;

use WMDE\Fundraising\Entities\Donation as DonationEntity;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
interface DonationRepository {

	/**
	 * TODO: add exception
	 *
	 * @param DonationEntity $donation
	 */
	public function storeDonation( DonationEntity $donation );

}
