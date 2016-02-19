<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Domain\Donation;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DonationRepositorySpy implements DonationRepository {

	private $donations = [];

	public function storeDonation( Donation $donation ) {
		$this->donations[] = $donation;
	}

	/**
	 * @return Donation[]
	 */
	public function getDonations(): array {
		return $this->donations;
	}

}
