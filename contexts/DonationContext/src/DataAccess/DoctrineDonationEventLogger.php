<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\DataAccess;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use WMDE\Fundraising\Entities\Donation as DoctrineDonation;
use WMDE\Fundraising\Frontend\DonationContext\Infrastructure\DonationEventLogException;
use WMDE\Fundraising\Frontend\DonationContext\Infrastructure\DonationEventLogger;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class DoctrineDonationEventLogger implements DonationEventLogger {

	private $entityManager;
	private $timestampFunction;

	public function __construct( EntityManager $entityManager, callable $timestampFunction = null ) {
		$this->entityManager = $entityManager;
		if ( is_null( $timestampFunction ) ) {
			$this->timestampFunction = function () {
				return date( 'Y-m-d H:i:s' );
			};
		} else {
			$this->timestampFunction = $timestampFunction;
		}
	}

	public function log( int $donationId, string $message ) {
		try {
			/** @var DoctrineDonation $donation */
			$donation = $this->entityManager->find( DoctrineDonation::class, $donationId );
		}
		catch ( ORMException $e ) {
			throw new DonationEventLogException( 'Could not get donation', $e );
		}

		if ( is_null( $donation ) ) {
			throw new DonationEventLogException( 'Could not find donation with id ' . $donationId );
		}

		$data = $donation->getDecodedData();
		if ( empty( $data['log'] ) ) {
			$data['log'] = [];
		}
		$data['log'][call_user_func( $this->timestampFunction )] = $message;
		$donation->encodeAndSetData( $data );

		try {
			$this->entityManager->persist( $donation );
			$this->entityManager->flush();
		}
		catch ( ORMException $e ) {
			throw new DonationEventLogException( 'Could not store donation', $e );
		}

	}

}