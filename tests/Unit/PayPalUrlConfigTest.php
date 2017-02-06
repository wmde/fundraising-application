<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests;

use WMDE\Fundraising\Frontend\Presentation\PayPalUrlConfig;

/**
 * @covers WMDE\Fundraising\Frontend\Presentation\PayPalUrlConfig
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PayPalUrlConfigTest extends \PHPUnit\Framework\TestCase {

	public function testGivenIncompletePayPalUrlConfig_exceptionIsThrown() {
		$this->expectException( \RuntimeException::class );
		$this->newIncompletePayPalUrlConfig();
	}

	private function newIncompletePayPalUrlConfig() {
		return PayPalUrlConfig::newFromConfig( [
			PayPalUrlConfig::CONFIG_KEY_BASE_URL => 'http://that.paymentprovider.com/?',
			PayPalUrlConfig::CONFIG_KEY_ACCOUNT_ADDRESS => 'some@email-adress.com',
			PayPalUrlConfig::CONFIG_KEY_NOTIFY_URL => 'http://my.donation.app/handler/paypal/',
			PayPalUrlConfig::CONFIG_KEY_RETURN_URL => 'http://my.donation.app/donation/confirm/',
			PayPalUrlConfig::CONFIG_KEY_CANCEL_URL => 'http://my.donation.app/donation/cancel/',
			PayPalUrlConfig::CONFIG_KEY_ITEM_NAME => ''
		] );
	}

	public function testGivenValidPayPalUrlConfig_payPalConfigIsReturned() {
		$this->assertInstanceOf( PayPalUrlConfig::class, $this->newPayPalUrlConfig() );
	}

	private function newPayPalUrlConfig() {
		return PayPalUrlConfig::newFromConfig( [
			PayPalUrlConfig::CONFIG_KEY_BASE_URL => 'http://that.paymentprovider.com/?',
			PayPalUrlConfig::CONFIG_KEY_ACCOUNT_ADDRESS => 'some@email-adress.com',
			PayPalUrlConfig::CONFIG_KEY_NOTIFY_URL => 'http://my.donation.app/handler/paypal/',
			PayPalUrlConfig::CONFIG_KEY_RETURN_URL => 'http://my.donation.app/donation/confirm/',
			PayPalUrlConfig::CONFIG_KEY_CANCEL_URL => 'http://my.donation.app/donation/cancel/',
			PayPalUrlConfig::CONFIG_KEY_ITEM_NAME => 'This appears on the invoice'
		] );
	}

}
