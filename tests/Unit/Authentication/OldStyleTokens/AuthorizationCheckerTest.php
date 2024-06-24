<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Authentication\OldStyleTokens;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WMDE\Clock\Clock;
use WMDE\Clock\StubClock;
use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\AuthenticationToken;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\AuthorizationChecker;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\TokenRepository;
use WMDE\Fundraising\Frontend\Tests\Fixtures\InMemoryTokenRepository;

#[CoversClass( AuthorizationChecker::class )]
class AuthorizationCheckerTest extends TestCase {
	private const ACCESS_TOKEN = '123-access-token';
	private const UPDATE_TOKEN = '456-update-token';

	private const DONATION_ID = 20;
	private const MEMBERSHIP_ID = 32;

	private Clock $now;

	public function setUp(): void {
		$this->now = new StubClock( new \DateTimeImmutable() );
	}

	public function testGivenEmptyTokenCanAccessReturnsFalse(): void {
		$checker = new AuthorizationChecker(
			new InMemoryTokenRepository(),
			self::UPDATE_TOKEN,
			self::ACCESS_TOKEN,
			$this->now
		);

		$this->assertFalse( $checker->canAccessDonation( self::DONATION_ID ) );
	}

	public function testGivenNonMatchingTokenCanAccessDonationReturnsFalse(): void {
		$checker = new AuthorizationChecker(
			$this->givenTokenRepository(),
			'',
			'let-me-in',
			$this->now
		);

		$this->assertFalse( $checker->canAccessDonation( self::DONATION_ID ) );
	}

	public function testGivenMatchingTokenCanAccessDonationReturnsTrue(): void {
		$checker = new AuthorizationChecker(
			$this->givenTokenRepository(),
			'',
			self::ACCESS_TOKEN,
			$this->now
		);

		$this->assertTrue( $checker->canAccessDonation( self::DONATION_ID ) );
	}

	public function testGivenEmptyTokenCanUpdateDonationReturnsFalse(): void {
		$checker = new AuthorizationChecker(
			new InMemoryTokenRepository(),
			self::UPDATE_TOKEN,
			self::ACCESS_TOKEN,
			$this->now
		);

		$this->assertFalse( $checker->userCanModifyDonation( self::DONATION_ID ) );
	}

	public function testGivenNonMatchingTokenUserCanModifyDonationReturnsFalse(): void {
		$checker = new AuthorizationChecker(
			$this->givenTokenRepository(),
			'let-me-change',
			'',
			$this->now
		);

		$this->assertFalse( $checker->userCanModifyDonation( self::DONATION_ID ) );
	}

	public function testGivenMatchingExpiredTokenUserCanModifyDonationReturnsFalse(): void {
		$checker = new AuthorizationChecker(
			$this->givenTokenRepository(),
			self::UPDATE_TOKEN,
			'',
			new StubClock( $this->givenTimeInTheFuture( 5 ) )
		);

		$this->assertFalse( $checker->userCanModifyDonation( self::DONATION_ID ) );
	}

	public function testGivenMatchingAndNonExpiredTokenUserCanModifyDonationReturnsTrue(): void {
		$checker = new AuthorizationChecker(
			$this->givenTokenRepository(),
			self::UPDATE_TOKEN,
			'',
			$this->now
		);

		$this->assertTrue( $checker->userCanModifyDonation( self::DONATION_ID ) );
	}

	public function testSystemCanModifyDonationIgnoresTokenExpiry(): void {
		$checker = new AuthorizationChecker(
			$this->givenTokenRepository(),
			self::UPDATE_TOKEN,
			'',
			new StubClock( $this->givenTimeInTheFuture( 5 ) )
		);

		$this->assertTrue( $checker->systemCanModifyDonation( self::DONATION_ID ) );
	}

	public function givenTokenNotFoundCanAccessMembershipReturnsFalse(): void {
		$checker = new AuthorizationChecker(
			$this->givenTokenRepository(),
			self::UPDATE_TOKEN,
			self::ACCESS_TOKEN,
			$this->now
		);

		$this->assertFalse( $checker->canAccessMembership( self::MEMBERSHIP_ID ) );
	}

	public function testGivenNonMatchingTokenCanAccessMembershipReturnsFalse(): void {
		$checker = new AuthorizationChecker(
			$this->givenTokenRepository(),
			'',
			'let-me-see',
			$this->now
		);

		$this->assertFalse( $checker->canAccessMembership( self::MEMBERSHIP_ID ) );
	}

	public function testGivenMatchingTokenCanAccessMembershipReturnsTrue(): void {
		$checker = new AuthorizationChecker(
			$this->givenTokenRepository(),
			'',
			self::ACCESS_TOKEN,
			$this->now
		);

		$this->assertTrue( $checker->canAccessMembership( self::MEMBERSHIP_ID ) );
	}

	public function testGivenEmptyTokenCanModifyMembershipReturnsFalse(): void {
		$checker = new AuthorizationChecker(
			new InMemoryTokenRepository(),
			self::UPDATE_TOKEN,
			self::ACCESS_TOKEN,
			$this->now
		);

		$this->assertFalse( $checker->canModifyMembership( self::MEMBERSHIP_ID ) );
	}

	public function testGivenNonMatchingTokenCanModifyMembershipReturnsFalse(): void {
		$checker = new AuthorizationChecker(
			$this->givenTokenRepository(),
			'let-me-change',
			'',
			$this->now
		);

		$this->assertFalse( $checker->canModifyMembership( self::MEMBERSHIP_ID ) );
	}

	public function testGivenMatchingTokenCanModifyMembershipReturnsTrue(): void {
		$checker = new AuthorizationChecker(
			$this->givenTokenRepository(),
			self::UPDATE_TOKEN,
			'',
			$this->now
		);

		$this->assertTrue( $checker->canModifyMembership( self::MEMBERSHIP_ID ) );
	}

	public function testGivenMatchingTokenCanModifyMembershipIgnoresExpiry(): void {
		$checker = new AuthorizationChecker(
			$this->givenTokenRepository(),
			self::UPDATE_TOKEN,
			'',
			new StubClock( $this->givenTimeInTheFuture( 100 ) )
		);

		$this->assertTrue( $checker->canModifyMembership( self::MEMBERSHIP_ID ) );
	}

	private function givenTokenRepository(): TokenRepository {
		$expiry = $this->givenTimeInTheFuture( 1 );
		return new InMemoryTokenRepository(
			new AuthenticationToken(
				self::DONATION_ID,
				AuthenticationBoundedContext::Donation,
				self::ACCESS_TOKEN,
				self::UPDATE_TOKEN,
				$expiry
			),
			new AuthenticationToken(
				self::MEMBERSHIP_ID,
				AuthenticationBoundedContext::Membership,
				self::ACCESS_TOKEN,
				self::UPDATE_TOKEN,
				$expiry
			)
		);
	}

	private function givenTimeInTheFuture( int $hoursInTheFuture ): \DateTimeImmutable {
		return ( new \DateTimeImmutable() )
			->add( new \DateInterval( 'PT' . $hoursInTheFuture . 'H' ) );
	}

}
