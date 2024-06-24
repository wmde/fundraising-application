<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Authentication\OldStyleTokens;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\AuthenticationLoader;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\AuthenticationToken;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\NullToken;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\TokenRepository;
use WMDE\Fundraising\Frontend\Tests\Fixtures\InMemoryTokenRepository;

#[CoversClass( AuthenticationLoader::class )]
class AuthenticationLoaderTest extends TestCase {
	private const DONATION_ID = 3;
	private const MEMBERSHIP_ID = 6;

	public function testUrlAuthenticatorFactoriesReturnUrlAuthenticators(): void {
		$loader = new AuthenticationLoader( $this->givenATokenRepositoryWithTokens() );

		$donationAuthenticator = $loader->getDonationUrlAuthenticator( self::DONATION_ID );
		$authenticatedDonationUrl = $donationAuthenticator->addAuthenticationTokensToApplicationUrl( 'https://example.com/' );
		$membershipAuthenticator = $loader->getMembershipUrlAuthenticator( self::MEMBERSHIP_ID );
		$authenticatedMembershipUrl = $membershipAuthenticator->addAuthenticationTokensToApplicationUrl( 'https://example.com/' );

		$this->assertStringContainsString( 'accessToken=donation-access-token', $authenticatedDonationUrl );
		$this->assertStringContainsString( 'id=' . self::DONATION_ID, $authenticatedDonationUrl );
		$this->assertStringContainsString( 'accessToken=membership-access-token', $authenticatedMembershipUrl );
		$this->assertStringContainsString( 'id=' . self::MEMBERSHIP_ID, $authenticatedMembershipUrl );
	}

	public function testParameterAddingMethodsAddTokenParameters(): void {
		$loader = new AuthenticationLoader( $this->givenATokenRepositoryWithTokens() );

		$donationParameters = $loader->addDonationAuthorizationParameters( self::DONATION_ID, [ 'foo' => 1 ] );
		$membershipParameters = $loader->addMembershipAuthorizationParameters( self::MEMBERSHIP_ID, [ 'bar' => 2 ] );

		$this->assertSame(
			[
				'foo' => 1,
				'accessToken' => 'donation-access-token',
				'updateToken' => 'donation-update-token'
			],
			$donationParameters
		);
		$this->assertSame(
			[
				'bar' => 2,
				'accessToken' => 'membership-access-token',
				'updateToken' => 'membership-update-token'
			],
			$membershipParameters
		);
	}

	public function testLoaderMethodWillFailIfNoTokenIsFound(): void {
		$loader = new AuthenticationLoader( new InMemoryTokenRepository() );

		$this->expectException( \LogicException::class );
		$this->expectExceptionMessage( NullToken::getErrorMessage( self::DONATION_ID, AuthenticationBoundedContext::Donation ) );

		$loader->getDonationUrlAuthenticator( self::DONATION_ID );
	}

	private function givenATokenRepositoryWithTokens(): TokenRepository {
		return new InMemoryTokenRepository(
			new AuthenticationToken(
				self::DONATION_ID,
				AuthenticationBoundedContext::Donation,
				'donation-access-token',
				'donation-update-token',
			),
			new AuthenticationToken(
				self::MEMBERSHIP_ID,
				AuthenticationBoundedContext::Membership,
				'membership-access-token',
				'membership-update-token',
			),
		);
	}
}
