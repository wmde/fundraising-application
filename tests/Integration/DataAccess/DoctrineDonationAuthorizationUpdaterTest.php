<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\DataAccess;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineDonationAuthorizationUpdater;
use WMDE\Fundraising\Frontend\Infrastructure\AuthorizationUpdateException;
use WMDE\Fundraising\Frontend\Infrastructure\DonationAuthorizationUpdater;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @covers WMDE\Fundraising\Frontend\DataAccess\DoctrineDonationAuthorizationUpdater
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineDonationAuthorizationUpdaterTest extends \PHPUnit_Framework_TestCase {

	const ACCESS_TOKEN = 'kindly allow me access';
	const UPDATE_TOKEN = 'kindly accept my datas';
	const EXPIRY_TIME = '2150-12-07 00:00:00'; // v=ADV9XzgpQZc :)

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	public function setUp() {
		$this->entityManager = TestEnvironment::newInstance()->getFactory()->getEntityManager();
	}

	public function testWhenDonationExists_updateTokenAndExpiryGetSet() {
		$donation = new Donation();

		$this->persistDonation( $donation );

		$this->newAuthorizationUpdater()->allowModificationViaToken(
			$donation->getId(),
			self::UPDATE_TOKEN,
			new \DateTime( self::EXPIRY_TIME )
		);

		$this->assertDonationHasUpdateTokenAndExpiry(
			$donation->getId(),
			self::UPDATE_TOKEN,
			self::EXPIRY_TIME
		);
	}

	private function newAuthorizationUpdater(): DonationAuthorizationUpdater {
		return new DoctrineDonationAuthorizationUpdater( $this->entityManager );
	}

	private function persistDonation( Donation $donation ) {
		$this->entityManager->persist( $donation );
		$this->entityManager->flush();
	}

	private function assertDonationHasUpdateTokenAndExpiry( int $donationId, string $token, string $expiry ) {
		$donation = $this->getDonationById( $donationId );

		$this->assertSame( $token, $donation->getDataObject()->getUpdateToken() );
		$this->assertSame( $expiry, $donation->getDataObject()->getUpdateTokenExpiry() );
	}

	private function getDonationById( int $donationId ): Donation {
		return $this->entityManager->find( Donation::class, $donationId );
	}

	public function testWhenDonationDoesNotExist_assigningUpdateTokenCausesException() {
		$this->expectException( AuthorizationUpdateException::class );

		$this->newAuthorizationUpdater()->allowModificationViaToken(
			1337,
			self::UPDATE_TOKEN,
			new \DateTime( self::EXPIRY_TIME )
		);
	}

	public function testWhenDonationDoesNotExist_assigningAccessTokenCausesException() {
		$this->expectException( AuthorizationUpdateException::class );

		$this->newAuthorizationUpdater()->allowAccessViaToken(
			1337,
			self::ACCESS_TOKEN
		);
	}

	public function testWhenDatabaseReadFails_exceptionIsThrown() {
		$this->markTestIncomplete( 'TODO: make read method throw' ); // TODO

		$donation = new Donation();
		$this->persistDonation( $donation );

		$this->expectException( AuthorizationUpdateException::class );

		$this->newAuthorizationUpdater()->allowModificationViaToken(
			$donation->getId(),
			self::UPDATE_TOKEN,
			new \DateTime( self::EXPIRY_TIME )
		);
	}

	public function testWhenDatabaseWriteFails_exceptionIsThrown() {
		$this->markTestIncomplete( 'TODO: make write method throw' ); // TODO

		$donation = new Donation();
		$this->persistDonation( $donation );

		$this->expectException( AuthorizationUpdateException::class );

		$this->newAuthorizationUpdater()->allowModificationViaToken(
			$donation->getId(),
			self::UPDATE_TOKEN,
			new \DateTime( self::EXPIRY_TIME )
		);
	}

	public function testWhenDonationExists_accessTokenGetsSet() {
		$donation = new Donation();

		$this->persistDonation( $donation );

		$this->newAuthorizationUpdater()->allowAccessViaToken(
			$donation->getId(),
			self::ACCESS_TOKEN
		);

		$donation = $this->getDonationById( $donation->getId() );
		$this->assertSame( self::ACCESS_TOKEN, $donation->getDataObject()->getAccessToken() );
	}

}
