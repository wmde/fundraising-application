<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Authentication\OldStyleTokens;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\AuthenticationToken;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\PersistentAuthorizer;
use WMDE\Fundraising\Frontend\Authentication\Token;
use WMDE\Fundraising\Frontend\Authentication\TokenGenerator;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;
use WMDE\Fundraising\Frontend\Tests\Fixtures\InMemoryTokenRepository;

/**
 * @covers \WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\PersistentAuthorizer
 */
class PersistentDonationAuthorizerTest extends TestCase {
	private const DONATION_ID = 7;

	public function testGivenEmptyPersistenceAuthorizeDonationAccessWillCreateNewToken(): void {
		$repo = new InMemoryTokenRepository();
		$tokenGenerator = new FixedTokenGenerator( Token::fromHex( FixedTokenGenerator::DEFAULT_TOKEN ) );
		$updateTokenExpiry = new \DateInterval( 'PT1H' );
		$authorizer = new PersistentAuthorizer( $repo, $tokenGenerator, $updateTokenExpiry );

		$authorizer->authorizeDonationAccess( self::DONATION_ID );

		$token = $repo->getTokenById( self::DONATION_ID, AuthenticationBoundedContext::Donation );
		$this->assertNotNull( $token );
		$this->assertSame( FixedTokenGenerator::DEFAULT_TOKEN, $token->accessToken );
		$this->assertSame( FixedTokenGenerator::DEFAULT_TOKEN, $token->updateToken );
	}

	public function testExistingTokenAuthorizeDonationAccessWillReadExistingToken(): void {
		$repo = new InMemoryTokenRepository( new AuthenticationToken(
			self::DONATION_ID,
			AuthenticationBoundedContext::Donation,
			'existing-access-token',
			'existing-update-token',
			new \DateTimeImmutable( '2020-01-01' )
		) );
		$tokenGenerator = $this->createMock( TokenGenerator::class );
		$tokenGenerator->expects( $this->never() )->method( 'generateToken' );
		$updateTokenExpiry = new \DateInterval( 'PT1H' );
		$authorizer = new PersistentAuthorizer( $repo, $tokenGenerator, $updateTokenExpiry );

		$authorizer->authorizeDonationAccess( self::DONATION_ID );
	}

}
