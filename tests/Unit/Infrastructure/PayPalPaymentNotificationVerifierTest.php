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

	public function testReceiverAddressMismatches_verifierThrowsException() {
		$this->expectException( PayPalPaymentNotificationVerifierException::class );

		$this->newVerifier( new Client() )->verify( [
			'receiver_email' => self::INVALID_ACCOUNT_EMAIL,
			'payment_status' => self::VALID_PAYMENT_STATUS
		] );
	}

	public function testReceiverAddressNotGiven_verifierReturnsFalse() {
		$this->expectException( PayPalPaymentNotificationVerifierException::class );

		$this->newVerifier( new Client() )->verify( [] );
	}

	public function testPaymentStatusNotGiven_verifierReturnsFalse() {
		$this->expectException( PayPalPaymentNotificationVerifierException::class );
		$this->newVerifier( new Client() )->verify( [
			'receiver_email' => self::VALID_ACCOUNT_EMAIL
		] );
	}

	public function testPaymentStatusNotConfirmable_verifierReturnsFalse() {
		$this->expectException( PayPalPaymentNotificationVerifierException::class );
		$this->newVerifier( new Client() )->verify( [
			'receiver_email' => self::VALID_ACCOUNT_EMAIL,
			'payment_status' => self::INVALID_PAYMENT_STATUS,
		] );
	}

	public function testReassuringReceivedDataSucceeds_verifierReturnsTrue() {
		$verifier = $this->newVerifier( $this->newSucceedingClient() );
		$this->assertTrue( $verifier->verify( [
			'receiver_email' => self::VALID_ACCOUNT_EMAIL,
			'payment_status' => self::VALID_PAYMENT_STATUS
		] ) );
	}

	public function testReassuringReceivedDataFails_verifierReturnsFalse() {
		$this->expectException( PayPalPaymentNotificationVerifierException::class );
		$verifier = $this->newVerifier( $this->newFailingClient() );
		$verifier->verify( [
			'receiver_email' => self::VALID_ACCOUNT_EMAIL,
			'payment_status' => self::VALID_PAYMENT_STATUS
		] );
	}

	private function newVerifier( Client $httpClient ): PayPalPaymentNotificationVerifier {
		return new PayPalPaymentNotificationVerifier(
			$httpClient,
			[
				'base-url' => self::DUMMY_API_URL,
				'account-address' => self::VALID_ACCOUNT_EMAIL
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

	private function newClient( Stream $body ): Client {
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

		$client->expects( $this->once() )
			->method( 'post' )
			->with( self::DUMMY_API_URL, [
				'cmd' => '_notify_validate',
				'receiver_email' => self::VALID_ACCOUNT_EMAIL,
				'payment_status' => self::VALID_PAYMENT_STATUS
			] )
			->willReturn( $response );

		return $client;
	}

}
