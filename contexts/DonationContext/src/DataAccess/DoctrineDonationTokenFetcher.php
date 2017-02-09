<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\DataAccess;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\DonationContext\Authorization\DonationTokenFetcher;
use WMDE\Fundraising\Frontend\DonationContext\Authorization\DonationTokenFetchingException;
use WMDE\Fundraising\Frontend\DonationContext\Authorization\DonationTokens;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineDonationTokenFetcher implements DonationTokenFetcher {

	private $entityManager;

	public function __construct( EntityManager $entityManager ) {
		$this->entityManager = $entityManager;
	}

	/**
	 * @param int $donationId
	 *
	 * @return DonationTokens
	 * @throws DonationTokenFetchingException
	 */
	public function getTokens( int $donationId ): DonationTokens {
		$donation = $this->getDonationById( $donationId );

		return new DonationTokens(
			$donation->getDataObject()->getAccessToken(),
			$donation->getDataObject()->getUpdateToken()
		);
	}

	private function getDonationById( int $donationId ): ?Donation {
		return $this->entityManager->find( Donation::class, $donationId );
	}

}
