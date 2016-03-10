<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DataAccess;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\Infrastructure\AuthorizationChecker;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineTokenAuthorizationChecker implements AuthorizationChecker {

	private $entityManager;
	private $updateToken;

	public function __construct( EntityManager $entityManager, string $updateToken ) {
		$this->entityManager = $entityManager;
		$this->updateToken = $updateToken;
	}

	public function canModifyDonation( int $donationId ): bool {
		try {
			/**
			 * @var Donation $donation
			 */
			$donation = $this->entityManager->find( Donation::class, $donationId );
		}
		catch ( ORMException $ex ) {
			// TODO: might want to log failure here
			return false;
		}

		if ( $donation === null ) {
			return false;
		}

		return $this->tokenMatches( $donation )
			&& $this->tokenHasNotExpired( $donation );
	}

	private function tokenMatches( Donation $donation ): bool {
		return $donation->getDataObject()->getUpdateToken() === $this->updateToken;
	}

	private function tokenHasNotExpired( Donation $donation ): bool {
		$expiryTime = $donation->getDataObject()->getUpdateTokenExpiry();
		return $expiryTime !== null && strtotime( $expiryTime ) >= time();
	}

}
