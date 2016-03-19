<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\DataAccess;

use Codeception\Specify;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineDonationAuthorizer;
use WMDE\Fundraising\Frontend\Infrastructure\DonationAuthorizer;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @covers WMDE\Fundraising\Frontend\DataAccess\DoctrineDonationAuthorizer
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineDonationAuthorizerTest extends \PHPUnit_Framework_TestCase {
	use Specify;

	const CORRECT_UPDATE_TOKEN = 'CorrectUpdateToken';
	const CORRECT_ACCESS_TOKEN = 'CorrectAccessToken';
	const WRONG__UPDATE_TOKEN = 'WrongUpdateToken';
	const WRONG_ACCESS_TOKEN = 'WrongAccessToken';
	const MEANINGLESS_TOKEN = 'Some token';
	const MEANINGLESS_DONATION_ID = 1337;
	const ID_OF_WRONG_DONATION = 42;

	private function newAuthorizationServiceWithDonations( string $updateToken = null,
		string $accessToken = null, Donation ...$donations ): DonationAuthorizer {

		$entityManager = TestEnvironment::newInstance()->getFactory()->getEntityManager();

		foreach ( $donations as $donation ) {
			$entityManager->persist( $donation );
		}

		$entityManager->flush();

		return new DoctrineDonationAuthorizer( $entityManager, $updateToken, $accessToken );
	}

	public function testWhenNoDonations() {
		$this->specify( 'update authorization fails', function() {
			$authorizer = $this->newAuthorizationServiceWithDonations( self::CORRECT_UPDATE_TOKEN );
			$this->assertFalse( $authorizer->canModifyDonation( self::MEANINGLESS_DONATION_ID ) );
		} );

		$this->specify( 'access authorization fails', function() {
			$authorizer = $this->newAuthorizationServiceWithDonations( self::CORRECT_ACCESS_TOKEN );
			$this->assertFalse( $authorizer->canAccessDonation( self::MEANINGLESS_DONATION_ID ) );
		} );
	}

	public function testWhenDonationWithTokenExists() {
		$donation = new Donation();
		$donationData = $donation->getDataObject();
		$donationData->setUpdateToken( self::CORRECT_UPDATE_TOKEN );
		$donationData->setUpdateTokenExpiry( $this->getExpiryTimeInTheFuture() );
		$donationData->setAccessToken( self::CORRECT_ACCESS_TOKEN );
		$donation->setDataObject( $donationData );

		$this->specify(
			'given correct donation id and correct token, update authorization succeeds',
			function() use ( $donation ) {
				$authorizer = $this->newAuthorizationServiceWithDonations( self::CORRECT_UPDATE_TOKEN, null, $donation );
				$this->assertTrue( $authorizer->canModifyDonation( $donation->getId() ) );
			}
		);

		$this->specify(
			'given wrong donation id and correct token, update authorization fails',
			function() use ( $donation ) {
				$authorizer = $this->newAuthorizationServiceWithDonations( self::CORRECT_UPDATE_TOKEN, null, $donation );
				$this->assertFalse( $authorizer->canModifyDonation( self::ID_OF_WRONG_DONATION ) );
			}
		);

		$this->specify(
			'given correct donation id and wrong token, update authorization fails',
			function() use ( $donation ) {
				$authorizer = $this->newAuthorizationServiceWithDonations( self::WRONG__UPDATE_TOKEN, null, $donation );
				$this->assertFalse( $authorizer->canModifyDonation( $donation->getId() ) );
			}
		);

		$this->specify(
			'given correct donation id and correct token, access authorization succeeds',
			function() use ( $donation ) {
				$authorizer = $this->newAuthorizationServiceWithDonations( null, self::CORRECT_ACCESS_TOKEN, $donation );
				$this->assertTrue( $authorizer->canAccessDonation( $donation->getId() ) );
			}
		);

		$this->specify(
			'given wrong donation id and correct token, access authorization fails',
			function() use ( $donation ) {
				$authorizer = $this->newAuthorizationServiceWithDonations( null, self::CORRECT_ACCESS_TOKEN, $donation );
				$this->assertFalse( $authorizer->canAccessDonation( self::ID_OF_WRONG_DONATION ) );
			}
		);

		$this->specify(
			'given correct donation id and wrong token, access authorization fails',
			function() use ( $donation ) {
				$authorizer = $this->newAuthorizationServiceWithDonations( null, self::WRONG_ACCESS_TOKEN, $donation );
				$this->assertFalse( $authorizer->canAccessDonation( $donation->getId() ) );
			}
		);
	}

	private function getExpiryTimeInTheFuture(): string {
		return date( 'Y-m-d H:i:s', time() + 60 * 60 );
	}

	public function testWhenDonationWithoutTokenExists() {
		$donation = new Donation();
		$donation->encodeAndSetData( [] );

		$this->specify(
			'given correct donation id and a token, update authorization fails',
			function() use ( $donation ) {
				$authorizer = $this->newAuthorizationServiceWithDonations( self::MEANINGLESS_TOKEN, null, $donation );
				$this->assertFalse( $authorizer->canModifyDonation( $donation->getId() ) );
			}
		);

		$this->specify(
			'given correct donation id and a token, access authorization fails',
			function() use ( $donation ) {
				$authorizer = $this->newAuthorizationServiceWithDonations( null, self::MEANINGLESS_TOKEN, $donation );
				$this->assertFalse( $authorizer->canAccessDonation( $donation->getId() ) );
			}
		);
	}

	public function testWhenUpdateTokenIsExpired() {
		$donation = new Donation();
		$donationData = $donation->getDataObject();
		$donationData->setUpdateToken( self::CORRECT_UPDATE_TOKEN );
		$donationData->setUpdateTokenExpiry( $this->getExpiryTimeInThePast() );
		$donation->setDataObject( $donationData );

		$this->specify(
			'given correct donation id and a token, update authorization fails',
			function() use ( $donation ) {
				$authorizer = $this->newAuthorizationServiceWithDonations( self::CORRECT_UPDATE_TOKEN, null, $donation );
				$this->assertFalse( $authorizer->canModifyDonation( $donation->getId() ) );
			}
		);
	}

	private function getExpiryTimeInThePast(): string {
		return date( 'Y-m-d H:i:s', time() - 1 );
	}

}
