<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests;

use WMDE\Fundraising\Frontend\Domain\Model\Euro;
use WMDE\Fundraising\Frontend\Presentation\PayPalConfig;
use WMDE\Fundraising\Frontend\Presentation\PayPalUrlGenerator;

/**
 * @covers WMDE\Fundraising\Frontend\Presentation\PayPalUrlGenerator
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PayPalUrlGeneratorTest extends \PHPUnit_Framework_TestCase {

	const BASE_URL = 'https://www.sandbox.paypal.com/cgi-bin/webscr?';

	public function testSubscriptions() {
		$generator = new PayPalUrlGenerator( $this->newPayPalConfig() );

		$this->assertSame(
			'https://www.sandbox.paypal.com/cgi-bin/webscr' .
			'?cmd=_xclick-subscriptions' .
			'&no_shipping=1' .
			'&src=1' .
			'&sra=1' .
			'&srt=0' .
			'&a3=12.34' .
			'&p3=3' .
			'&t3=M' .
			'&business=foerderpp%40wikimedia.de' .
			'&currency_code=EUR' .
			'&lc=de' .
			'&item_name=Mentioning+that+awesome+organization+on+the+invoice' .
			'&item_number=1234' .
			'&notify_url=http%3A%2F%2Fmy.donation.app%2Fhandler%2Fpaypal%2F' .
			'&cancel_return=http%3A%2F%2Fmy.donation.app%2Fdonation%2Fcancel%2F' .
			'&return=http%3A%2F%2Fmy.donation.app%2Fdonation%2Fconfirm%2F%3Fsid%3D1234' .
			'&custom=%7B%22sid%22%3A1234%2C%22utoken%22%3A%22utoken%22%7D',

			$generator->generateUrl( 1234, Euro::newFromString( '12.34' ), 3, 'utoken' )
		);
	}

	public function testSinglePayments() {
		$generator = new PayPalUrlGenerator( $this->newPayPalConfig() );

		$this->assertSame(
			'https://www.sandbox.paypal.com/cgi-bin/webscr' .
			'?cmd=_donations' .
			'&amount=12.34' .
			'&business=foerderpp%40wikimedia.de' .
			'&currency_code=EUR' .
			'&lc=de' .
			'&item_name=Mentioning+that+awesome+organization+on+the+invoice' .
			'&item_number=1234' .
			'&notify_url=http%3A%2F%2Fmy.donation.app%2Fhandler%2Fpaypal%2F' .
			'&cancel_return=http%3A%2F%2Fmy.donation.app%2Fdonation%2Fcancel%2F' .
			'&return=http%3A%2F%2Fmy.donation.app%2Fdonation%2Fconfirm%2F%3Fsid%3D1234' .
			'&custom=%7B%22sid%22%3A1234%2C%22utoken%22%3A%22utoken%22%7D',

			$generator->generateUrl( 1234, Euro::newFromString( '12.34' ), 0, 'utoken' )
		);
	}

	private function newPayPalConfig(): PayPalConfig {
		return PayPalConfig::newFromConfig( [
			'base-url' => self::BASE_URL,
			'account-address' => 'foerderpp@wikimedia.de',
			'notify-url' => 'http://my.donation.app/handler/paypal/',
			'return-url' => 'http://my.donation.app/donation/confirm/',
			'cancel-url' => 'http://my.donation.app/donation/cancel/',
			'item-name' => 'Mentioning that awesome organization on the invoice'
		] );
	}

	public function testGivenIncompletePayPalConfig_exceptionIsThrown() {
		$this->expectException( \RuntimeException::class );
		$this->newIncompletePayPalConfig();
	}

	private function newIncompletePayPalConfig() {
		return PayPalConfig::newFromConfig( [
			'base-url' => self::BASE_URL,
			'account-address' => 'some@email-adress.com',
			'notify-url' => 'http://my.donation.app/handler/paypal/',
			'return-url' => 'http://my.donation.app/donation/confirm/',
			'cancel-url' => 'http://my.donation.app/donation/cancel/',
			'item-name' => ''
		] );
	}

}
