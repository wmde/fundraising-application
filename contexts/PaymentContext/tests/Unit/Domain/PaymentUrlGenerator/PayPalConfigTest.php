<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\PaymentContext\Domain\PaymentUrlGenerator;

use WMDE\Fundraising\Frontend\PaymentContext\Domain\PaymentUrlGenerator\PayPalConfig;

/**
 * @covers \WMDE\Fundraising\Frontend\PaymentContext\Domain\PaymentUrlGenerator\PayPalConfig
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PayPalConfigTest extends \PHPUnit\Framework\TestCase {

	public function testGivenIncompletePayPalUrlConfig_exceptionIsThrown(): void {
		$this->expectException( \RuntimeException::class );
		$this->newIncompletePayPalUrlConfig();
	}

	private function newIncompletePayPalUrlConfig(): PayPalConfig {
		return PayPalConfig::newFromConfig( [
			PayPalConfig::CONFIG_KEY_BASE_URL => 'http://that.paymentprovider.com/?',
			PayPalConfig::CONFIG_KEY_ACCOUNT_ADDRESS => 'some@email-adress.com',
			PayPalConfig::CONFIG_KEY_NOTIFY_URL => 'http://my.donation.app/handler/paypal/',
			PayPalConfig::CONFIG_KEY_RETURN_URL => 'http://my.donation.app/donation/confirm/',
			PayPalConfig::CONFIG_KEY_CANCEL_URL => 'http://my.donation.app/donation/cancel/',
			PayPalConfig::CONFIG_KEY_ITEM_NAME => ''
		] );
	}

	public function testGivenValidPayPalUrlConfig_payPalConfigIsReturned(): void {
		$this->assertInstanceOf( PayPalConfig::class, $this->newPayPalUrlConfig() );
	}

	private function newPayPalUrlConfig(): PayPalConfig {
		return PayPalConfig::newFromConfig( [
			PayPalConfig::CONFIG_KEY_BASE_URL => 'http://that.paymentprovider.com/?',
			PayPalConfig::CONFIG_KEY_ACCOUNT_ADDRESS => 'some@email-adress.com',
			PayPalConfig::CONFIG_KEY_NOTIFY_URL => 'http://my.donation.app/handler/paypal/',
			PayPalConfig::CONFIG_KEY_RETURN_URL => 'http://my.donation.app/donation/confirm/',
			PayPalConfig::CONFIG_KEY_CANCEL_URL => 'http://my.donation.app/donation/cancel/',
			PayPalConfig::CONFIG_KEY_ITEM_NAME => 'This appears on the invoice'
		] );
	}

}
