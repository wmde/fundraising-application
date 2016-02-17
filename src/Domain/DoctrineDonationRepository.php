<?php

namespace WMDE\Fundraising\Frontend\Domain;

use Doctrine\DBAL\Connection;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Store\Factory as FundraisingStoreFactory;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DoctrineDonationRepository implements DonationRepository {

	private $entityManager;

	public function __construct( Connection $connection ) {
		$factory = new FundraisingStoreFactory( $connection );
		$this->entityManager = $factory->getEntityManager();
	}

	public function storeDonation( Donation $donation ) {
		$this->entityManager->persist( $donation );
	}

}