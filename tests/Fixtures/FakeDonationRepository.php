<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class FakeDonationRepository implements DonationRepository {

	private $calls = 0;
	private $donations = [];

	public function __construct( Donation ...$donations ) {
		foreach ( $donations as $donation ) {
			$this->storeDonation( $donation );
		}
	}

	public function storeDonation( Donation $donation ) {
		if ( $donation->getId() === null ) {
			$donation->assignId( ++$this->calls );
		}
		$this->donations[$donation->getId()] = $donation;
	}

	public function getDonationById( int $id ) {
		return array_key_exists( $id, $this->donations ) ? $this->donations[$id] : null;
	}

}
