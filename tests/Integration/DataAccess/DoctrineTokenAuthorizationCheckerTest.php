<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\DataAccess;

use Codeception\Specify;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineTokenAuthorizationChecker;
use WMDE\Fundraising\Frontend\Infrastructure\AuthorizationChecker;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @covers WMDE\Fundraising\Frontend\DataAccess\DoctrineTokenAuthorizationChecker
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineTokenAuthorizationCheckerTest extends \PHPUnit_Framework_TestCase {
	use Specify;

	const THE_CORRECT_TOKEN = 'TheCorrectToken';
	const WRONG_TOKEN = 'Wrong token';
	const MEANINGLESS_TOKEN = 'Some token';
	const MEANINGLESS_DONATION_ID = 1337;
	const ID_OF_WRONG_DONATION = 42;

	private function newAuthorizationServiceWithDonations( string $token, Donation ...$donations ): AuthorizationChecker {
		$entityManager = TestEnvironment::newInstance()->getFactory()->getEntityManager();

		foreach ( $donations as $donation ) {
			$entityManager->persist( $donation );
		}

		$entityManager->flush();

		return new DoctrineTokenAuthorizationChecker( $entityManager, $token );
	}

	public function testWhenNoDonations() {
		$this->specify( 'authorization fails', function() {
			$authorizer = $this->newAuthorizationServiceWithDonations( self::THE_CORRECT_TOKEN );
			$this->assertFalse( $authorizer->canModifyDonation( self::MEANINGLESS_DONATION_ID ) );
		} );
	}

	public function testWhenDonationWithTokenExists() {
		$donation = new Donation();
		$donationData = $donation->getDataObject();
		$donationData->setUpdateToken( self::THE_CORRECT_TOKEN );
		$donationData->setUpdateTokenExpiry( $this->getExpiryTimeInTheFuture() );
		$donation->setDataObject( $donationData );

		$this->specify(
			'given correct donation id and correct token, authorization succeeds',
			function() use ( $donation ) {
				$authorizer = $this->newAuthorizationServiceWithDonations( self::THE_CORRECT_TOKEN, $donation );
				$this->assertTrue( $authorizer->canModifyDonation( $donation->getId() ) );
			}
		);

		$this->specify(
			'given wrong donation id and correct token, authorization fails',
			function() use ( $donation ) {
				$authorizer = $this->newAuthorizationServiceWithDonations( self::THE_CORRECT_TOKEN, $donation );
				$this->assertFalse( $authorizer->canModifyDonation( self::ID_OF_WRONG_DONATION ) );
			}
		);

		$this->specify(
			'given correct donation id and wrong token, authorization fails',
			function() use ( $donation ) {
				$authorizer = $this->newAuthorizationServiceWithDonations( self::WRONG_TOKEN, $donation );
				$this->assertFalse( $authorizer->canModifyDonation( $donation->getId() ) );
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
			'given correct donation id and a token, authorization fails',
			function() use ( $donation ) {
				$authorizer = $this->newAuthorizationServiceWithDonations( self::MEANINGLESS_TOKEN, $donation );
				$this->assertFalse( $authorizer->canModifyDonation( $donation->getId() ) );
			}
		);
	}

	public function testWhenUpdateTokenIsExpired() {
		$donation = new Donation();
		$donationData = $donation->getDataObject();
		$donationData->setUpdateToken( self::THE_CORRECT_TOKEN );
		$donationData->setUpdateTokenExpiry( $this->getExpiryTimeInThePast() );
		$donation->setDataObject( $donationData );

		$this->specify(
			'given correct donation id and a token, authorization fails',
			function() use ( $donation ) {
				$authorizer = $this->newAuthorizationServiceWithDonations( self::THE_CORRECT_TOKEN, $donation );
				$this->assertFalse( $authorizer->canModifyDonation( $donation->getId() ) );
			}
		);
	}

	private function getExpiryTimeInThePast(): string {
		return date( 'Y-m-d H:i:s', time() - 1 );
	}

}
