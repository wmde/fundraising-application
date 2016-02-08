<?php

namespace WMDE\Fundraising\Frontend\Domain;

use WMDE\Fundraising\Entities\Donation;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
interface DonationRepository {
	public function storeDonation( Donation $donation );
}