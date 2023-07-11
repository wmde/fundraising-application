<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Data;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineEntities\Donation;

class CommentsForTesting {
	public static function persistFirstComment( EntityManager $entityManager ): void {
		$firstDonation = new Donation();
		$firstDonation->setId( 1 );
		$firstDonation->setPublicRecord( 'First name' );
		$firstDonation->setComment( 'First comment' );
		$firstDonation->setAmount( '100.42' );
		$firstDonation->setCreationTime( new \DateTime( '1984-01-01' ) );
		$firstDonation->setIsPublic( true );
		$firstDonation->setPaymentId( 1 );
		$entityManager->persist( $firstDonation );
	}

	public static function persistSecondComment( EntityManager $entityManager ): void {
		$secondDonation = new Donation();
		$secondDonation->setId( 2 );
		$secondDonation->setPublicRecord( 'Second name' );
		$secondDonation->setComment( 'Second comment' );
		$secondDonation->setAmount( '9001' );
		$secondDonation->setCreationTime( new \DateTime( '1984-02-02' ) );
		$secondDonation->setIsPublic( true );
		$secondDonation->setPaymentId( 2 );
		$entityManager->persist( $secondDonation );
	}

	public static function persistEvilComment( EntityManager $entityManager ): void {
		$maliciousDonation = new Donation();
		$maliciousDonation->setId( 3 );
		$maliciousDonation->setPublicRecord( 'Third name & company' );
		$maliciousDonation->setComment( 'Third <script> comment' );
		$maliciousDonation->setAmount( '9001' );
		$maliciousDonation->setCreationTime( new \DateTime( '1984-02-02' ) );
		$maliciousDonation->setIsPublic( true );
		$maliciousDonation->setPaymentId( 3 );
		$entityManager->persist( $maliciousDonation );
	}
}
