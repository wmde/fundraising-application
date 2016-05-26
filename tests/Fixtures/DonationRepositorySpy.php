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
class DonationRepositorySpy extends FakeDonationRepository {

	private $storeDonationCalls = [];
	private $getDonationCalls = [];

	public function __construct( Donation ...$donations ) {
		parent::__construct( ...$donations );
		$this->storeDonationCalls = []; // remove calls coming from initialization
	}

	public function storeDonation( Donation $donation ) {
		$this->storeDonationCalls[] = clone( $donation ); // protect against the donation being changed later
		parent::storeDonation( $donation );
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
		return parent::getDonationById( $id );
	}

	/**
	 * @return int[]
	 */
	public function getGetDonationCalls(): array {
		return $this->getDonationCalls;
	}

}
