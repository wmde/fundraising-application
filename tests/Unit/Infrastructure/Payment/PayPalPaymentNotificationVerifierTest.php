<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Payment;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use WMDE\Fundraising\Frontend\Infrastructure\Payment\PayPalPaymentNotificationVerifier;
use WMDE\Fundraising\Frontend\Infrastructure\Payment\PayPalPaymentNotificationVerifierException;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\Payment\PayPalPaymentNotificationVerifier
 * @license GPL-2.0-or-later
 */
class PayPalPaymentNotificationVerifierTest extends \PHPUnit\Framework\TestCase {

	private const VALID_ACCOUNT_EMAIL = 'foerderpp@wikimedia.de';
	private const INVALID_ACCOUNT_EMAIL = 'this.is.not@my.email.address';
	private const DUMMY_API_URL = 'https://dummy-url.com';
	private const VALID_PAYMENT_STATUS = 'Completed';
	private const INVALID_PAYMENT_STATUS = 'Unknown';
	private const ITEM_NAME = 'My donation';
	private const CURRENCY_EUR = 'EUR';
	private const RECURRING_NO_PAYMENT = 'recurring_payment_suspended_due_to_max_failed_payment';

	private $expectedRequestparameters;
	private $receivedRequests;

	protected function setUp(): void {
		$this->expectedRequestparameters = [];
		$this->receivedRequests = [];
	}

	public function testReceiverAddressMismatches_verifierThrowsException(): void {
		$this->expectException( PayPalPaymentNotificationVerifierException::class );

		$this->newVerifier( new Client() )->verify( [
			'receiver_email' => self::INVALID_ACCOUNT_EMAIL,
			'payment_status' => self::VALID_PAYMENT_STATUS
		] );
	}

	public function testReceiverAddressNotGiven_verifierThrowsException(): void {
		$this->expectException( PayPalPaymentNotificationVerifierException::class );

		$this->newVerifier( new Client() )->verify( [] );
	}

	public function testPaymentStatusNotGiven_verifierThrowsException(): void {
		$this->expectException( PayPalPaymentNotificationVerifierException::class );
		$this->newVerifier( new Client() )->verify( [
			'receiver_email' => self::VALID_ACCOUNT_EMAIL
		] );
	}

	public function testPaymentStatusNotConfirmable_verifierThrowsException(): void {
		$this->expectException( PayPalPaymentNotificationVerifierException::class );
		$this->newVerifier( new Client() )->verify( [
			'receiver_email' => self::VALID_ACCOUNT_EMAIL,
			'payment_status' => self::INVALID_PAYMENT_STATUS,
		] );
	}

	public function testPaypalHttpCallSucceeds_verifierDoesNotThrowException(): void {
		$this->expectNotToPerformAssertions();
		try {
			$this->newVerifier( $this->newSucceedingClient() )->verify( $this->newRequest() );
		} catch ( PayPalPaymentNotificationVerifierException $e ) {
			$this->fail( 'There should be no exception with valid data and succeeding client.' );
		}
	}

	public function testPaypalHttpCallReturnsFailure_verifierThrowsException(): void {
		$this->expectException( PayPalPaymentNotificationVerifierException::class );
		$verifier = $this->newVerifier( $this->newClientWithErrorResponse() );
		$verifier->verify( $this->newRequest() );
	}

	public function testPaypalHttpCallFails_verifierThrowsException(): void {
		$this->expectException( PayPalPaymentNotificationVerifierException::class );
		$verifier = $this->newVerifier( $this->newFailingClient() );
		$verifier->verify( $this->newRequest() );
	}

	public function testPaypalHttpCallReturnsUnexpectedResponse_verifierThrowsException(): void {
		$this->expectException( PayPalPaymentNotificationVerifierException::class );
		$verifier = $this->newVerifier( $this->newClient( 'Ra-ra-rasputin, lover of the Russian queen!' ) );
		$verifier->verify( $this->newRequest() );
	}

	public function testGivenRecurringPaymentStatusMessage_currencyIsCheckedInDifferentField(): void {
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
			$this->assertVerificationParametersWereSent();
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
			self::DUMMY_API_URL,
			self::VALID_ACCOUNT_EMAIL
		);
	}

	private function newSucceedingClient(): Client {
		return $this->newClient( 'VERIFIED' );
	}

	private function newSucceedingClientExpectingParams( array $expectedParams ): Client {
		return $this->newClient( 'VERIFIED', $expectedParams );
	}

	private function newClientWithErrorResponse(): Client {
		return $this->newClient( 'INVALID' );
	}

	private function newClient( string $body, array $expectedParams = [] ): Client {
		$this->receivedRequests = [];
		$history = Middleware::history( $this->receivedRequests );
		$mock = new MockHandler( [
			new Response( 200, [], $body )
		] );
		$handlerStack = HandlerStack::create( $mock );
		$handlerStack->push( $history );
		$this->expectedRequestparameters = $expectedParams;
		return new Client( [ 'handler' => $handlerStack ] );
	}

	private function newFailingClient(): Client {
		$mock = new MockHandler( [
			new Response( 500, [], 'Internal Server Error - Paypal is overwhelmed' )
		] );
		$handlerStack = HandlerStack::create( $mock );
		return new Client( [ 'handler' => $handlerStack ] );
	}

	private function assertVerificationParametersWereSent(): void {
		if ( count( $this->expectedRequestparameters ) == 0 ) {
			return;
		}
		if ( count( $this->receivedRequests ) == 0 ) {
			$this->fail( 'No verification requests received' );
		}
		/** @var \GuzzleHttp\Psr7\Request $req */
		$req = $this->receivedRequests[0]['request'];
		parse_str( $req->getBody()->getContents(), $receivedArguments );

		$this->assertEquals( $this->expectedRequestparameters, $receivedArguments );
	}

}
