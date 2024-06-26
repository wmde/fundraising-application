<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Authentication\OldStyleTokens;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\AuthenticationToken;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\PersistentAuthorizer;
use WMDE\Fundraising\Frontend\Authentication\Token;
use WMDE\Fundraising\Frontend\Authentication\TokenGenerator;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;
use WMDE\Fundraising\Frontend\Tests\Fixtures\InMemoryTokenRepository;

#[CoversClass( PersistentAuthorizer::class )]
class PersistentDonationAuthorizerTest extends TestCase {
	private const DONATION_ID = 7;
	private const MEMBERSHIP_ID = 9;

	public function testGivenEmptyPersistenceAuthorizeDonationAccessWillCreateNewToken(): void {
		$repo = new InMemoryTokenRepository();
		$tokenGenerator = new FixedTokenGenerator( Token::fromHex( FixedTokenGenerator::DEFAULT_TOKEN ) );
		$updateTokenExpiry = new \DateInterval( 'PT1H' );
		$authorizer = new PersistentAuthorizer( $repo, $tokenGenerator, new NullLogger(), $updateTokenExpiry );

		$authorizer->authorizeDonationAccess( self::DONATION_ID );

		$token = $repo->getTokenById( self::DONATION_ID, AuthenticationBoundedContext::Donation );
		$this->assertSame( FixedTokenGenerator::DEFAULT_TOKEN, $token->getAccessToken() );
		$this->assertSame( FixedTokenGenerator::DEFAULT_TOKEN, $token->getAccessToken() );
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
		$authorizer = new PersistentAuthorizer( $repo, $tokenGenerator, $this->givenLoggerThatExpectsWarning(), $updateTokenExpiry );

		$authorizer->authorizeDonationAccess( self::DONATION_ID );
	}

	public function testGivenEmptyPersistenceAuthorizeMembershipAccessWillCreateNewToken(): void {
		$repo = new InMemoryTokenRepository();
		$tokenGenerator = new FixedTokenGenerator( Token::fromHex( FixedTokenGenerator::DEFAULT_TOKEN ) );
		$updateTokenExpiry = new \DateInterval( 'PT1H' );
		$authorizer = new PersistentAuthorizer( $repo, $tokenGenerator, new NullLogger(), $updateTokenExpiry );

		$authorizer->authorizeMembershipAccess( self::MEMBERSHIP_ID );

		$token = $repo->getTokenById( self::MEMBERSHIP_ID, AuthenticationBoundedContext::Membership );
		$this->assertSame( FixedTokenGenerator::DEFAULT_TOKEN, $token->getAccessToken() );
		$this->assertSame( FixedTokenGenerator::DEFAULT_TOKEN, $token->getUpdateToken() );
	}

	public function testExistingTokenAuthorizeMembershipAccessWillReadExistingToken(): void {
		$repo = new InMemoryTokenRepository( new AuthenticationToken(
			self::MEMBERSHIP_ID,
			AuthenticationBoundedContext::Membership,
			'existing-access-token',
			'existing-update-token',
			new \DateTimeImmutable( '2020-01-01' )
		) );
		$tokenGenerator = $this->createMock( TokenGenerator::class );
		$tokenGenerator->expects( $this->never() )->method( 'generateToken' );
		$updateTokenExpiry = new \DateInterval( 'PT1H' );
		$authorizer = new PersistentAuthorizer( $repo, $tokenGenerator, $this->givenLoggerThatExpectsWarning(), $updateTokenExpiry );

		$authorizer->authorizeMembershipAccess( self::MEMBERSHIP_ID );
	}

	public function givenLoggerThatExpectsWarning(): LoggerInterface {
		$logger = $this->createMock( LoggerInterface::class );
		$logger->expects( $this->once() )
			->method( 'warning' )
			->with( $this->matchesRegularExpression( '/Found existing token/' ) );
		return $logger;
	}

}
