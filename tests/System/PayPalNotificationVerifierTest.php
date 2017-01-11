<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\System;

use GuzzleHttp\Client;
use WMDE\Fundraising\Frontend\Infrastructure\PayPalPaymentNotificationVerifier;
use WMDE\Fundraising\Frontend\Infrastructure\PayPalPaymentNotificationVerifierException;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PayPalNotificationVerifierTest extends \PHPUnit_Framework_TestCase {

	/** @var PayPalPaymentNotificationVerifier */
	private $verifier;

	public function setUp() {
		$config = TestEnvironment::newInstance( [] )->getConfig();
		$this->verifier = $this->newVerifier( $config['paypal-donation'] );
	}

	public function testWhenVerifyingInvalidRequest_externalServiceReturnsAnError() {
		$this->expectException( PayPalPaymentNotificationVerifierException::class );
		$this->expectExceptionMessageRegExp( '/did not confirm/' );

		$this->verifier->verify( $this->newPostRequest() );
	}

	public function testWhenVerifyingValidRequest_externalServiceReturnsAnError() {
		$this->expectException( PayPalPaymentNotificationVerifierException::class );
		$this->expectExceptionMessageRegExp( '/did not confirm/' );

		$this->verifier->verify( $this->newPostRequest() );
	}

	private function newVerifier( array $config ): PayPalPaymentNotificationVerifier {
		return new PayPalPaymentNotificationVerifier(
			new Client(),
			$config['base-url'],
			$config['account-address']
		);
	}

	private function newPostRequest() {
		return [
			'mc_gross' => '5.00',
			'protection_eligibility' => 'Eligible',
			'address_status' => 'unconfirmed',
			'payer_id' => '9WUHGV9BWZWVL',
			'tax' => '0.00',
			'address_street' => 'ESpachstr. 1',
			'payment_date' => '05:18:50 May 10, 2016 PDT',
			'payment_status' => 'Completed',
			'charset' => 'windows-1252',
			'address_zip' => '79111',
			'first_name' => 'Till',
			'mc_fee' => '0.45',
			'address_country_code' => 'DE',
			'address_name' => 'Till Mletzko',
			'notify_version' => '3.8',
			'custom' => '{"sid":2370,"utoken":"6bce065f9e82d1f9c38a166cf0e7d50d"}',
			'payer_status' => 'verified',
			'business' => 'paypaldev-facilitator@wikimedia.de',
			'address_country' => 'Germany',
			'address_city' => 'Freiburg',
			'quantity' => '0',
			'verify_sign' => 'AP37U7gPUL7wW.bhj7gUYHr1YqBVAj5HrO-IU5Zjfe7DVMnQUQ25Q7Se',
			'payer_email' => 'paypaldevtest@wikimedia.de',
			'txn_id' => '7M153721ST378624W',
			'payment_type' => 'instant',
			'last_name' => 'Mletzko',
			'address_state' => 'Empty',
			'receiver_email' => 'paypaldev-facilitator@wikimedia.de',
			'payment_fee' => '',
			'receiver_id' => '58L5J2X6AD9RJ',
			'txn_type' => 'web_accept',
			'item_name' => 'This appears on the invoice',
			'mc_currency' => 'EUR',
			'item_number' => '2370',
			'residence_country' => 'DE',
			'test_ipn' => '1',
			'transaction_subject' => '{"sid":2370,"utoken":"6bce065f9e82d1f9c38a166cf0e7d50d"}',
			'payment_gross' => '',
			'ipn_track_id' => 'a0701bdb5aa3'
		];
	}

}