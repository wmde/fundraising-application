<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\DonatingContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\DonatingContext\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\DonatingContext\Domain\Repositories\GetDonationException;
use WMDE\Fundraising\Frontend\DonatingContext\Domain\Repositories\StoreDonationException;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class FakeDonationRepository implements DonationRepository {

	private $calls = 0;
	private $donations = [];
	private $throwOnRead = false;
	private $throwOnWrite = false;

	public function __construct( Donation ...$donations ) {
		foreach ( $donations as $donation ) {
			$this->storeDonation( $donation );
		}
	}

	public function throwOnRead() {
		$this->throwOnRead = true;
	}

	public function throwOnWrite() {
		$this->throwOnWrite = true;
	}

	public function storeDonation( Donation $donation ) {
		if ( $this->throwOnWrite ) {
			throw new StoreDonationException();
		}

		if ( $donation->getId() === null ) {
			$donation->assignId( ++$this->calls );
		}
		$this->donations[$donation->getId()] = unserialize( serialize( $donation ) );
	}

	public function getDonationById( int $id ) {
		if ( $this->throwOnRead ) {
			throw new GetDonationException();
		}

		if ( array_key_exists( $id, $this->donations ) ) {
			return unserialize( serialize( $this->donations[$id] ) );
		}

		return null;
	}

}
