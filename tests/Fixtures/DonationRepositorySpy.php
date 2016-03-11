<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\Domain\Repositories\GetDonationException;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DonationRepositorySpy implements DonationRepository {

	private $storeDonationCalls = [];
	private $getDonationCalls = [];

	public function storeDonation( Donation $donation ) {
		$this->storeDonationCalls[] = $donation;
	}

	/**
	 * @return Donation[]
	 */
	public function getStoreDonationCalls(): array {
		return $this->storeDonationCalls;
	}

	/**
	 * @param int $id
	 *
	 * @return Donation|null
	 * @throws GetDonationException
	 */
	public function getDonationById( int $id ) {
		$this->getDonationCalls[] = $id;
	}

	/**
	 * @return int[]
	 */
	public function getGetDonationCalls(): array {
		return $this->getDonationCalls;
	}

}
