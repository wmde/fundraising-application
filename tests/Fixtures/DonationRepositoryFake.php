<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DonationRepositoryFake implements DonationRepository {

	private $calls = 0;

	public function storeDonation( Donation $donation ) {
		$donation->setId( ++$this->calls );
	}

	public function getDonationById( int $id ) {
		return null;
	}

}
