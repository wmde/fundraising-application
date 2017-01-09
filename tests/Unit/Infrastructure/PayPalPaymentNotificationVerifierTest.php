<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use WMDE\Fundraising\Frontend\Infrastructure\PayPalPaymentNotificationVerifier;
use WMDE\Fundraising\Frontend\Infrastructure\PayPalPaymentNotificationVerifierException;

/**
 * @covers WMDE\Fundraising\Frontend\Infrastructure\PayPalPaymentNotificationVerifier
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PayPalPaymentNotificationVerifierTest extends \PHPUnit_Framework_TestCase {

	const VALID_ACCOUNT_EMAIL = 'foerderpp@wikimedia.de';
	const INVALID_ACCOUNT_EMAIL = 'this.is.not@my.email.address';
	const DUMMY_API_URL = 'https://dummy-url.com';
	const VALID_PAYMENT_STATUS = 'Completed';
	const INVALID_PAYMENT_STATUS = 'Unknown';
	const ITEM_NAME = 'My donation';
	const CURRENCY_EUR = 'EUR';
	const RECURRING_NO_PAYMENT = 'recurring_payment_suspended_due_to_max_failed_payment';

	public function testReceiverAddressMismatches_verifierThrowsException() {
		$this->expectException( PayPalPaymentNotificationVerifierException::class );

		$this->newVerifier( new Client() )->verify( [
			'receiver_email' => self::INVALID_ACCOUNT_EMAIL,
			'payment_status' => self::VALID_PAYMENT_STATUS
		] );
	}

	public function testReceiverAddressNotGiven_verifierThrowsException() {
		$this->expectException( PayPalPaymentNotificationVerifierException::class );

		$this->newVerifier( new Client() )->verify( [] );
	}

	public function testPaymentStatusNotGiven_verifierThrowsException() {
		$this->expectException( PayPalPaymentNotificationVerifierException::class );
		$this->newVerifier( new Client() )->verify( [
			'receiver_email' => self::VALID_ACCOUNT_EMAIL
		] );
	}

	public function testPaymentStatusNotConfirmable_verifierThrowsException() {
		$this->expectException( PayPalPaymentNotificationVerifierException::class );
		$this->newVerifier( new Client() )->verify( [
			'receiver_email' => self::VALID_ACCOUNT_EMAIL,
			'payment_status' => self::INVALID_PAYMENT_STATUS,
		] );
	}

	public function testReassuringReceivedDataSucceeds_verifierDoesNotThrowException() {
		try {
			$this->newVerifier( $this->newSucceedingClient() )->verify( $this->newRequest() );
		} catch ( PayPalPaymentNotificationVerifierException $e ) {
			$this->fail( 'There should be no exception with valid data and succeeding client.' );
		}
	}

	public function testReassuringReceivedDataFails_verifierThrowsException() {
		$this->expectException( PayPalPaymentNotificationVerifierException::class );
		$verifier = $this->newVerifier( $this->newFailingClient() );
		$verifier->verify( $this->newRequest() );
	}

	public function testGivenRecurringPaymentStatusMessage_currencyIsCheckedInDifferentField() {
		try {
			$expectedParams = [
				'cmd' => '_notify-validate',
				'receiver_email' => self::VALID_ACCOUNT_EMAIL,
				'payment_status' => self::VALID_PAYMENT_STATUS,
				'item_name' => self::ITEM_NAME,
				'txn_type' => self::RECURRING_NO_PAYMENT,
				'currency_code' => self::CURRENCY_EUR
			];
			$this->newVerifier( $this->newSucceedingClientExpectingParams( $expectedParams ) )
				->verify( $this->newFailedRecurringPaymentRequest() );
		} catch ( PayPalPaymentNotificationVerifierException $e ) {
			$this->fail( 'Currency in different field should be ok for non-payment-complete recurring notices.' );
		}
	}

	private function newRequest(): array {
		return [
			'receiver_email' => self::VALID_ACCOUNT_EMAIL,
			'payment_status' => self::VALID_PAYMENT_STATUS,
			'item_name' => self::ITEM_NAME,
			'mc_currency' => self::CURRENCY_EUR
		];
	}

	private function newFailedRecurringPaymentRequest(): array {
		return [
			'receiver_email' => self::VALID_ACCOUNT_EMAIL,
			'payment_status' => self::VALID_PAYMENT_STATUS,
			'item_name' => self::ITEM_NAME,
			'txn_type' => self::RECURRING_NO_PAYMENT,
			'currency_code' => self::CURRENCY_EUR
		];
	}

	private function newVerifier( Client $httpClient ): PayPalPaymentNotificationVerifier {
		return new PayPalPaymentNotificationVerifier(
			$httpClient,
			[
				'base-url' => self::DUMMY_API_URL,
				'account-address' => self::VALID_ACCOUNT_EMAIL,
				'item-name' => self::ITEM_NAME
			]
		);
	}

	private function newSucceedingClient(): Client {
		$body = $this->getMockBuilder( Stream::class )
			->disableOriginalConstructor()
			->setMethods( [ 'getContents' ] )
			->getMock();

		$body->expects( $this->once() )
			->method( 'getContents' )
			->willReturn( 'VERIFIED' );

		return $this->newClient( $body );
	}

	private function newSucceedingClientExpectingParams( array $expectedParams ): Client {
		$body = $this->getMockBuilder( Stream::class )
			->disableOriginalConstructor()
			->setMethods( [ 'getContents' ] )
			->getMock();

		$body->expects( $this->once() )
			->method( 'getContents' )
			->willReturn( 'VERIFIED' );

		return $this->newClient( $body, $expectedParams );
	}

	private function newFailingClient(): Client {
		$body = $this->getMockBuilder( Stream::class )
			->disableOriginalConstructor()
			->setMethods( [ 'getContents' ] )
			->getMock();

		$body->expects( $this->once() )
			->method( 'getContents' )
			->willReturn( 'INVALID' );

		return $this->newClient( $body );
	}

	private function newClient( Stream $body, array $expectedParams = null ): Client {
		$response = $this->getMockBuilder( Response::class )
			->disableOriginalConstructor()
			->setMethods( [ 'getBody' ] )
			->getMock();

		$response->expects( $this->once() )
			->method( 'getBody' )
			->willReturn( $body );

		$client = $this->getMockBuilder( Client::class )
			->disableOriginalConstructor()
			->setMethods( [ 'post' ] )
			->getMock();

		if ( is_null( $expectedParams ) ) {
			$expectedParams = [
				'cmd' => '_notify-validate',
				'receiver_email' => self::VALID_ACCOUNT_EMAIL,
				'payment_status' => self::VALID_PAYMENT_STATUS,
				'item_name' => self::ITEM_NAME,
				'mc_currency' => self::CURRENCY_EUR
			];
		}
		$client->expects( $this->once() )
			->method( 'post' )
			->with( self::DUMMY_API_URL, [
				'form_params' => $expectedParams
			] )
			->willReturn( $response );

		return $client;
	}

}
