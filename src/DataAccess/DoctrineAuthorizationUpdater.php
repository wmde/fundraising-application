<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DataAccess;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\Infrastructure\AuthorizationUpdateException;
use WMDE\Fundraising\Frontend\Infrastructure\AuthorizationUpdater;
use WMDE\Fundraising\Store\DonationData;

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

		$donation->modifyDataObject( function( DonationData $data ) use ( $token, $expiry ) {
			$data->setUpdateToken( $token );
			$data->setUpdateTokenExpiry( $expiry->format( self::DATE_TIME_FORMAT ) );
		} );

		$this->persistDonation( $donation );
	}

	private function getDonationById( int $donationId ): Donation {
		try {
			$donation = $this->entityManager->find( Donation::class, $donationId );
		}
		catch ( ORMException $ex ) {
			// TODO: might want to log failure here
			throw new AuthorizationUpdateException( 'Donation could not be accessed' );
		}

		if ( $donation === null ) {
			throw new AuthorizationUpdateException( 'Donation does not exist' );
		}

		return $donation;
	}

	private function persistDonation( Donation $donation ) {
		try {
			$this->entityManager->persist( $donation );
			$this->entityManager->flush();
		}
		catch ( ORMException $ex ) {
			throw new AuthorizationUpdateException( 'Failed to persist the donation' );
		}
	}

	/**
	 * @throws AuthorizationUpdateException
	 */
	public function allowDonationAccessViaToken( int $donationId, string $accessToken ) {
		$donation = $this->getDonationById( $donationId );

		$donation->modifyDataObject( function( DonationData $data ) use ( $accessToken ) {
			$data->setAccessToken( $accessToken );
		} );

		$this->persistDonation( $donation );
	}

}
