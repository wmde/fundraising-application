<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests;

use WMDE\Fundraising\Frontend\Presentation\PayPalConfig;

/**
 * @covers WMDE\Fundraising\Frontend\Presentation\PayPalConfig
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PayPalConfigTest extends \PHPUnit_Framework_TestCase {

	public function testGivenIncompletePayPalConfig_exceptionIsThrown() {
		$this->expectException( \RuntimeException::class );
		$this->newIncompletePayPalConfig();
	}

	private function newIncompletePayPalConfig() {
		return PayPalConfig::newFromConfig( [
			PayPalConfig::CONFIG_KEY_BASE_URL => 'http://that.paymentprovider.com/?',
			PayPalConfig::CONFIG_KEY_ACCOUNT_ADDRESS => 'some@email-adress.com',
			PayPalCOnfig::CONFIG_KEY_NOTIFY_URL => 'http://my.donation.app/handler/paypal/',
			PayPalCOnfig::CONFIG_KEY_RETURN_URL => 'http://my.donation.app/donation/confirm/',
			PayPalCOnfig::CONFIG_KEY_CANCEL_URL => 'http://my.donation.app/donation/cancel/',
			PayPalCOnfig::CONFIG_KEY_ITEM_NAME => ''
		] );
	}

	public function testGivenValidPayPalConfig_payPalConfigIsReturned() {
		$this->assertInstanceOf( PayPalConfig::class, $this->newPayPalConfig() );
	}

	private function newPayPalConfig() {
		return PayPalConfig::newFromConfig( [
			PayPalConfig::CONFIG_KEY_BASE_URL => 'http://that.paymentprovider.com/?',
			PayPalConfig::CONFIG_KEY_ACCOUNT_ADDRESS => 'some@email-adress.com',
			PayPalCOnfig::CONFIG_KEY_NOTIFY_URL => 'http://my.donation.app/handler/paypal/',
			PayPalCOnfig::CONFIG_KEY_RETURN_URL => 'http://my.donation.app/donation/confirm/',
			PayPalCOnfig::CONFIG_KEY_CANCEL_URL => 'http://my.donation.app/donation/cancel/',
			PayPalCOnfig::CONFIG_KEY_ITEM_NAME => 'This appears on the invoice'
		] );
	}

}
