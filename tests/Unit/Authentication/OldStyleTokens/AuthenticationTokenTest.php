<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Authentication\OldStyleTokens;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\AuthenticationToken;

#[CoversClass( AuthenticationToken::class )]
class AuthenticationTokenTest extends TestCase {
	public function testTokenCanCheckExpiry(): void {
		$token = new AuthenticationToken(
			1,
			AuthenticationBoundedContext::Donation,
			'access-token',
			'update-token',
			new \DateTimeImmutable( '2019-01-01 23:59:59' )
		);

		$this->assertTrue( $token->updateTokenHasExpired( new \DateTimeImmutable( '2019-01-02 0:00:00' ) ) );
		$this->assertTrue( $token->updateTokenHasExpired( new \DateTimeImmutable( '2019-01-03' ) ) );
		$this->assertFalse( $token->updateTokenHasExpired( new \DateTimeImmutable( '2019-01-01 23:59:59' ) ) );
		$this->assertFalse( $token->updateTokenHasExpired( new \DateTimeImmutable( '2019-01-01 23:59:58' ) ) );
		$this->assertFalse( $token->updateTokenHasExpired( new \DateTimeImmutable( '2018-12-31' ) ) );
	}

	public function testTokenNeverExpiresWhenNoExpiryDateIsGiven(): void {
		$token = new AuthenticationToken(
			1,
			AuthenticationBoundedContext::Donation,
			'access-token',
			'update-token',
			null
		);

		$this->assertFalse( $token->updateTokenHasExpired( new \DateTimeImmutable( '2019-01-01 23:59:59' ) ) );
		$this->assertFalse( $token->updateTokenHasExpired( new \DateTimeImmutable( '1970-01-01' ) ) );
		$this->assertFalse( $token->updateTokenHasExpired( new \DateTimeImmutable( '2099-12-31' ) ) );
	}

	public function testAccessTokenMatches(): void {
		$token = new AuthenticationToken(
			1,
			AuthenticationBoundedContext::Donation,
			'access-token',
			'update-token',
			null
		);

		$this->assertTrue( $token->accessTokenMatches( 'access-token' ) );
		$this->assertFalse( $token->accessTokenMatches( 'wrong-token' ) );
	}

	public function testUpdateTokenMatches(): void {
		$token = new AuthenticationToken(
			1,
			AuthenticationBoundedContext::Donation,
			'access-token',
			'update-token',
			null
		);

		$this->assertTrue( $token->updateTokenMatches( 'update-token' ) );
		$this->assertFalse( $token->updateTokenMatches( 'wrong-token' ) );
	}

	public function testAccessAndUpdateTokenGetters(): void {
		$token = new AuthenticationToken(
			1,
			AuthenticationBoundedContext::Donation,
			'access-token-147',
			'update-token-258',
			null
		);

		$this->assertSame( 'access-token-147', $token->getAccessToken() );
		$this->assertSame( 'update-token-258', $token->getUpdateToken() );
	}
}
