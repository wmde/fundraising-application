<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures;

use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\GetDonationException;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\StoreDonationException;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class FakeDonationRepository implements DonationRepository {

	private $calls = 0;
	private $donations = [];
	private $donationClones = [];
	private $throwOnRead = false;
	private $throwOnWrite = false;

	public function __construct( Donation ...$donations ) {
		foreach ( $donations as $donation ) {
			$this->storeDonation( $donation );
		}
	}

	public function throwOnRead(): void {
		$this->throwOnRead = true;
	}

	public function throwOnWrite(): void {
		$this->throwOnWrite = true;
	}

	public function storeDonation( Donation $donation ): void {
		if ( $this->throwOnWrite ) {
			throw new StoreDonationException();
		}

		if ( $donation->getId() === null ) {
			$donation->assignId( ++$this->calls );
		}

		$this->donations[$donation->getId()] = $donation;
		// guard against memory-modification after store
		$this->donationClones[$donation->getId()] = clone $donation;
	}

	public function getDonationById( int $id ): ?Donation {
		if ( $this->throwOnRead ) {
			throw new GetDonationException();
		}

		if ( array_key_exists( $id, $this->donations ) ) {
			if ( serialize( $this->donationClones[$id] ) !== serialize( $this->donations[$id] ) ) {
				// return object value at the time of storing
				return $this->donationClones[$id];
			}
			// return original object (===)
			return $this->donations[$id];
		}

		return null;
	}

}
