<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\DataAccess;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\DonationContext\Authorization\DonationAuthorizer;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineDonationAuthorizer implements DonationAuthorizer {

	private $entityManager;
	private $updateToken;
	private $accessToken;

	public function __construct( EntityManager $entityManager, string $updateToken = null, string $accessToken = null ) {
		$this->entityManager = $entityManager;
		$this->updateToken = $updateToken;
		$this->accessToken = $accessToken;
	}

	/**
	 * Check if donation exists, has matching token and token is not expired
	 * @param int $donationId
	 * @return bool
	 */
	public function userCanModifyDonation( int $donationId ): bool {
		try {
			$donation = $this->getDonationById( $donationId );
		}
		catch ( ORMException $ex ) {
			// TODO: might want to log failure here
			return false;
		}

		return $donation !== null
			&& $this->updateTokenMatches( $donation )
			&& $this->tokenHasNotExpired( $donation );
	}

	/**
	 * Check if donation exists and has matching token
	 * @param int $donationId
	 * @return bool
	 */
	public function systemCanModifyDonation( int $donationId ): bool {
		try {
			$donation = $this->getDonationById( $donationId );
		}
		catch ( ORMException $ex ) {
			// TODO: might want to log failure here
			return false;
		}

		return $donation !== null
			&& $this->updateTokenMatches( $donation );
	}

	/**
	 * @param int $donationId
	 *
	 * @return Donation|null
	 */
	private function getDonationById( int $donationId ) {
		return $this->entityManager->find( Donation::class, $donationId );
	}

	private function updateTokenMatches( Donation $donation ): bool {
		return $donation->getDataObject()->getUpdateToken() === $this->updateToken;
	}

	private function tokenHasNotExpired( Donation $donation ): bool {
		$expiryTime = $donation->getDataObject()->getUpdateTokenExpiry();
		return $expiryTime !== null && strtotime( $expiryTime ) >= time();
	}

	public function canAccessDonation( int $donationId ): bool {
		try {
			$donation = $this->getDonationById( $donationId );
		}
		catch ( ORMException $ex ) {
			// TODO: might want to log failure here
			return false;
		}

		return $donation !== null
			&& $this->accessTokenMatches( $donation );
	}

	private function accessTokenMatches( Donation $donation ): bool {
		return $donation->getDataObject()->getAccessToken() === $this->accessToken;
	}

}
