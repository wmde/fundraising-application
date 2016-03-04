<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests;

use WMDE\Fundraising\Frontend\Domain\PayPalConfig;

/**
 * @covers WMDE\Fundraising\Frontend\Domain\PayPalConfig
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
			'base-url' => 'http://that.paymentprovider.com/?',
			'account-address' => 'some@email-adress.com',
			'notify-url' => 'http://my.donation.app/handler/paypal/',
			'return-url' => 'http://my.donation.app/donation/confirm/',
			'cancel-url' => 'http://my.donation.app/donation/cancel/',
			'item-name' => ''
		] );
	}

	public function testGivenValidPayPalConfig_payPalConfigIsReturned() {
		$this->assertInstanceOf( PayPalConfig::class, $this->newPayPalConfig() );
	}

	private function newPayPalConfig() {
		return PayPalConfig::newFromConfig( [
			'base-url' => 'http://that.paymentprovider.com/?',
			'account-address' => 'some@email-adress.com',
			'notify-url' => 'http://my.donation.app/handler/paypal/',
			'return-url' => 'http://my.donation.app/donation/confirm/',
			'cancel-url' => 'http://my.donation.app/donation/cancel/',
			'item-name' => 'This appears on the invoice'
		] );
	}

}
