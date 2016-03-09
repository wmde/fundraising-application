<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\DataAccess;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineAuthorizationUpdater;
use WMDE\Fundraising\Frontend\Infrastructure\AuthorizationUpdater;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @covers WMDE\Fundraising\Frontend\DataAccess\DoctrineAuthorizationUpdater
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineAuthorizationUpdaterTest extends \PHPUnit_Framework_TestCase {

	const UPDATE_TOKEN = 'kindly allow me access';
	const EXPIRY_TIME = '2150-12-07 00:00:00'; // v=ADV9XzgpQZc :)

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	public function setUp() {
		$this->entityManager = TestEnvironment::newInstance()->getFactory()->getEntityManager();
	}

	public function testWhenDonationExists_tokenAndExpiryGetSet() {
		$donation = new Donation();

		$this->persistDonation( $donation );

		$this->newAuthorizationUpdater()->allowDonationModificationViaToken(
			$donation->getId(),
			self::UPDATE_TOKEN,
			new \DateTime( self::EXPIRY_TIME )
		);

		$this->assertDonationHasTokenAndExpiry(
			$donation->getId(),
			self::UPDATE_TOKEN,
			self::EXPIRY_TIME
		);
	}

	private function newAuthorizationUpdater(): AuthorizationUpdater {
		return new DoctrineAuthorizationUpdater( $this->entityManager );
	}

	private function persistDonation( Donation $donation ) {
		$this->entityManager->persist( $donation );
		$this->entityManager->flush();
	}

	private function assertDonationHasTokenAndExpiry( int $donationId, string $token, string $expiry ) {
		$donation = $this->getDonationById( $donationId );

		$this->assertSame( $token, $donation->getDataObject()->getUpdateToken() );
		$this->assertSame( $expiry, $donation->getDataObject()->getUpdateTokenExpiry() );
	}

	private function getDonationById( int $donationId ): Donation {
		return $this->entityManager->find( Donation::class, $donationId );
	}

}
