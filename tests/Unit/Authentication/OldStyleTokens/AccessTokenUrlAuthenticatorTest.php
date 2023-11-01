<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Authentication\OldStyleTokens;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\AccessTokenUrlAuthenticator;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\AuthenticationToken;
use WMDE\Fundraising\PaymentContext\Domain\UrlGenerator\PaymentCompletionURLGenerator;
use WMDE\Fundraising\PaymentContext\Services\PaymentUrlGenerator\CreditCardURLGenerator;
use WMDE\Fundraising\PaymentContext\Services\PaymentUrlGenerator\LegacyPayPalURLGenerator;
use WMDE\Fundraising\PaymentContext\Services\PaymentUrlGenerator\SofortURLGenerator;

/**
 * @covers \WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\AccessTokenUrlAuthenticator
 */
class AccessTokenUrlAuthenticatorTest extends TestCase {
	public function testAddAuthenticationTokensToUrlWithoutParameters(): void {
		$token = $this->makeToken();
		$authenticator = new AccessTokenUrlAuthenticator( $token );

		$authenticatedUrl = $authenticator->addAuthenticationTokensToApplicationUrl( 'https://example.com/' );

		$this->assertSame( 'https://example.com/?id=1&accessToken=4cc35570k3n', $authenticatedUrl );
	}

	public function testAddAuthenticationTokensToUrlWithParameters(): void {
		$token = $this->makeToken();
		$authenticator = new AccessTokenUrlAuthenticator( $token );

		$authenticatedUrl = $authenticator->addAuthenticationTokensToApplicationUrl( 'https://example.com/?lang=en_GB' );

		$this->assertSame( 'https://example.com/?lang=en_GB&id=1&accessToken=4cc35570k3n', $authenticatedUrl );
	}

	public function testGetAuthenticationTokenForPayPalPaymentProviderUrl(): void {
		$token = $this->makeToken();
		$authenticator = new AccessTokenUrlAuthenticator( $token );

		$returnedParameters = $authenticator->getAuthenticationTokensForPaymentProviderUrl( LegacyPayPalURLGenerator::class, [ 'custom' ] );

		$expectedParameters = [
			'custom' => '{"sid":1,"utoken":"vpd47370k3n"}'
		];
		$this->assertSame( $expectedParameters, $returnedParameters );
	}

	public function testGetAuthenticationTokenForCreditCardPaymentProviderUrl(): void {
		$token = $this->makeToken();
		$authenticator = new AccessTokenUrlAuthenticator( $token );

		$returnedParameters = $authenticator->getAuthenticationTokensForPaymentProviderUrl( CreditCardURLGenerator::class, [ 'utoken', 'token' ] );

		$expectedParameters = [
			'utoken' => 'vpd47370k3n',
			'token' => '4cc35570k3n'
		];
		$this->assertSame( $expectedParameters, $returnedParameters );
	}

	public function testGetAuthenticationTokenForSofortProviderUrl(): void {
		$token = $this->makeToken();
		$authenticator = new AccessTokenUrlAuthenticator( $token );

		$returnedParameters = $authenticator->getAuthenticationTokensForPaymentProviderUrl(
			SofortURLGenerator::class,
			[ 'id', 'updateToken' ]
		);

		$expectedParameters = [
			'id' => 1,
			'updateToken' => 'vpd47370k3n'
		];
		$this->assertSame( $expectedParameters, $returnedParameters );
	}

	public function testGetAuthenticationTokenForPaymentProviderUrlWillThrowExceptionIfUnknownUrlGeneratorClass(): void {
		$token = $this->makeToken();
		$authenticator = new AccessTokenUrlAuthenticator( $token );

		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessageMatches( '/Unsupported URL generator class/' );
		$authenticator->getAuthenticationTokensForPaymentProviderUrl( PaymentCompletionURLGenerator::class, [] );
	}

	public function testGetAuthenticationTokenForPaymentProviderUrlWillThrowExceptionIfExpectedParametersAreMissing(): void {
		$token = $this->makeToken();
		$authenticator = new AccessTokenUrlAuthenticator( $token );

		$this->expectException( \DomainException::class );
		$this->expectExceptionMessageMatches( "/'custom'/" );
		$this->expectExceptionMessageMatches( "/'customary'/" );
		$authenticator->getAuthenticationTokensForPaymentProviderUrl( LegacyPayPalURLGenerator::class, [ 'customary' ] );
	}

	/**
	 * @doesNotPerformAssertions (We're testing that no exception is thrown)
	 */
	public function testGetAuthenticationTokenForPaymentProviderUrlWillPassWhenUrlGeneratorCreatesMoreParametersThanExpected(): void {
		$token = $this->makeToken();
		$authenticator = new AccessTokenUrlAuthenticator( $token );

		$authenticator->getAuthenticationTokensForPaymentProviderUrl( LegacyPayPalURLGenerator::class, [] );
	}

	private function makeToken(): AuthenticationToken {
		return new AuthenticationToken(
			1,
			AuthenticationBoundedContext::Donation,
			'4cc35570k3n',
			'vpd47370k3n',
			new \DateTimeImmutable()
		);
	}
}
