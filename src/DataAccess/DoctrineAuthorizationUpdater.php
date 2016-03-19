<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DataAccess;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\Infrastructure\AuthorizationUpdateException;
use WMDE\Fundraising\Frontend\Infrastructure\AuthorizationUpdater;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineAuthorizationUpdater implements AuthorizationUpdater {

	const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

	private $entityManager;

	public function __construct( EntityManager $entityManager ) {
		$this->entityManager = $entityManager;
	}

	/**
	 * @throws AuthorizationUpdateException
	 */
	public function allowDonationModificationViaToken( int $donationId, string $token, \DateTime $expiry ) {
		$donation = $this->getDonationById( $donationId );

		if ( $donation === null ) {
			throw new AuthorizationUpdateException( 'Donation does not exist' );
		}

		$donationData = $donation->getDataObject();
		$donationData->setUpdateToken( $token );
		$donationData->setUpdateTokenExpiry( $expiry->format( self::DATE_TIME_FORMAT ) );
		$donation->setDataObject( $donationData );

		try {
			$this->entityManager->persist( $donation );
			$this->entityManager->flush();
		}
		catch ( ORMException $ex ) {
			throw new AuthorizationUpdateException( 'Failed to persist the token' );
		}
	}

	/**
	 * @param int $donationId
	 * @return Donation|null
	 */
	private function getDonationById( int $donationId ) {
		try {
			$donation = $this->entityManager->find( Donation::class, $donationId );
		}
		catch ( ORMException $ex ) {
			// TODO: might want to log failure here
			return null;
		}

		if ( $donation === null ) {
			return null;
		}

		return $donation;
	}

	/**
	 * @throws AuthorizationUpdateException
	 */
	public function allowDonationAccessViaToken( int $donationId, string $accessToken ) {
		// TODO
	}

}
