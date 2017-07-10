<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\PaymentContext\Domain\PaymentUrlGenerator;

use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\PaymentUrlGenerator\PayPalConfig;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\PaymentUrlGenerator\PayPal as PaypalUrlGenerator;

/**
 * @covers \WMDE\Fundraising\Frontend\PaymentContext\Domain\PaymentUrlGenerator\PayPal
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PayPalTest extends \PHPUnit\Framework\TestCase {

	const BASE_URL = 'https://www.sandbox.paypal.com/cgi-bin/webscr?';
	const ACCOUNT_ADDRESS = 'foerderpp@wikimedia.de';
	const NOTIFY_URL = 'http://my.donation.app/handler/paypal/';
	const RETURN_URL = 'http://my.donation.app/donation/confirm/';
	const CANCEL_URL = 'http://my.donation.app/donation/cancel/';
	const ITEM_NAME = 'Mentioning that awesome organization on the invoice';

	public function testSubscriptions(): void {
		$generator = new PayPalUrlGenerator( $this->newPayPalUrlConfig() );

		$this->assertUrlValidForSubscriptions(
			$generator->generateUrl( 1234, Euro::newFromString( '12.34' ), 3, 'utoken', 'atoken' )
		);
	}

	private function assertUrlValidForSubscriptions( string $generatedUrl ): void {
		$this->assertCommonParamsSet( $generatedUrl );
		$this->assertSubscriptionRelatedParamsSet( $generatedUrl );
	}

	public function testSinglePayments(): void {
		$generator = new PayPalUrlGenerator( $this->newPayPalUrlConfig() );

		$this->assertUrlValidForSinglePayments(
			$generator->generateUrl( 1234, Euro::newFromString( '12.34' ), 0, 'utoken', 'atoken' )
		);
	}

	private function assertUrlValidForSinglePayments( string $generatedUrl ): void {
		$this->assertCommonParamsSet( $generatedUrl );
		$this->assertSinglePaymentRelatedParamsSet( $generatedUrl );
	}

	private function newPayPalUrlConfig(): PayPalConfig {
		return PayPalConfig::newFromConfig( [
			'base-url' => self::BASE_URL,
			'account-address' => self::ACCOUNT_ADDRESS,
			'notify-url' => self::NOTIFY_URL,
			'return-url' => self::RETURN_URL,
			'cancel-url' => self::CANCEL_URL,
			'item-name' => self::ITEM_NAME
		] );
	}

	public function testGivenIncompletePayPalUrlConfig_exceptionIsThrown(): void {
		$this->expectException( \RuntimeException::class );
		$this->newIncompletePayPalUrlConfig();
	}

	private function newIncompletePayPalUrlConfig(): PayPalConfig {
		return PayPalConfig::newFromConfig( [
			'base-url' => self::BASE_URL,
			'account-address' => 'some@email-adress.com',
			'notify-url' => self::NOTIFY_URL,
			'return-url' => self::RETURN_URL,
			'cancel-url' => self::CANCEL_URL,
			'item-name' => ''
		] );
	}

	public function testDelayedSubscriptions(): void {
		$generator = new PayPalUrlGenerator( $this->newPayPalUrlConfigWithDelayedPayment() );

		$this->assertUrlValidForDelayedSubscriptions(
			$generator->generateUrl( 1234, Euro::newFromString( '12.34' ), 3, 'utoken', 'atoken' )
		);
	}

	private function assertUrlValidForDelayedSubscriptions( string $generatedUrl ): void {
		$this->assertCommonParamsSet( $generatedUrl );
		$this->assertSubscriptionRelatedParamsSet( $generatedUrl );
		$this->assertTrialPeriodRelatedParametersSet( $generatedUrl );
	}

	private function newPayPalUrlConfigWithDelayedPayment(): PayPalConfig {
		return PayPalConfig::newFromConfig( [
			'base-url' => self::BASE_URL,
			'account-address' => self::ACCOUNT_ADDRESS,
			'notify-url' => self::NOTIFY_URL,
			'return-url' => self::RETURN_URL,
			'cancel-url' => self::CANCEL_URL,
			'item-name' => self::ITEM_NAME,
			'delay-in-days' => 90
		] );
	}

	private function assertCommonParamsSet( string $generatedUrl ): void {
		$this->assertContains( 'https://www.sandbox.paypal.com/cgi-bin/webscr', $generatedUrl );
		$this->assertContains( 'business=foerderpp%40wikimedia.de', $generatedUrl );
		$this->assertContains( 'currency_code=EUR', $generatedUrl );
		$this->assertContains( 'lc=de_DE', $generatedUrl );
		$this->assertContains( 'item_name=Mentioning+that+awesome+organization+on+the+invoice', $generatedUrl );
		$this->assertContains( 'item_number=1234', $generatedUrl );
		$this->assertContains( 'notify_url=http%3A%2F%2Fmy.donation.app%2Fhandler%2Fpaypal%2F', $generatedUrl );
		$this->assertContains( 'cancel_return=http%3A%2F%2Fmy.donation.app%2Fdonation%2Fcancel%2F', $generatedUrl );
		$this->assertContains(
			'return=http%3A%2F%2Fmy.donation.app%2Fdonation%2Fconfirm%2F%3Fid%3D1234%26accessToken%3Datoken',
			$generatedUrl
		);
		$this->assertContains( 'custom=%7B%22sid%22%3A1234%2C%22utoken%22%3A%22utoken%22%7D', $generatedUrl );
	}

	private function assertSinglePaymentRelatedParamsSet( string $generatedUrl ): void {
		$this->assertContains( 'cmd=_donations', $generatedUrl );
		$this->assertContains( 'amount=12.34', $generatedUrl );
	}

	private function assertTrialPeriodRelatedParametersSet( string $generatedUrl ): void {
		$this->assertContains( 'a1=0', $generatedUrl );
		$this->assertContains( 'p1=90', $generatedUrl );
		$this->assertContains( 't1=D', $generatedUrl );
	}

	private function assertSubscriptionRelatedParamsSet( string $generatedUrl ): void {
		$this->assertContains( 'cmd=_xclick-subscriptions', $generatedUrl );
		$this->assertContains( 'no_shipping=1', $generatedUrl );
		$this->assertContains( 'src=1', $generatedUrl );
		$this->assertContains( 'sra=1', $generatedUrl );
		$this->assertContains( 'srt=0', $generatedUrl );
		$this->assertContains( 'a3=12.34', $generatedUrl );
		$this->assertContains( 'p3=3', $generatedUrl );
		$this->assertContains( 't3=M', $generatedUrl );
	}

}
