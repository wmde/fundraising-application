<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use WMDE\Fundraising\Frontend\Infrastructure\PayPalPaymentNotificationVerifier;

/**
 * @covers WMDE\Fundraising\Frontend\Infrastructure\PayPalPaymentNotificationVerifier
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PayPalPaymentNotificationVerifierTest extends \PHPUnit_Framework_TestCase {

	public function testReceiverAddressMismatches_verifierReturnsFalse() {
		$this->assertFalse( $this->newVerifier( null )->verify( [
			'receiver_email' => 'this.is.not@my.email.address',
			'payment_status' => 'Completed'
		] ) );
	}

	public function testReceiverAddressNotGiven_verifierReturnsFalse() {
		$this->assertFalse( $this->newVerifier( null )->verify( [] ) );
	}

	public function testPaymentStatusNotGiven_verifierReturnsFalse() {
		$this->assertFalse( $this->newVerifier( null )->verify( [
			'receiver_email' => 'foerderpp@wikimedia.de'
		] ) );
	}

	public function testPaymentStatusNotConfirmable_verifierReturnsFalse() {
		$this->assertFalse( $this->newVerifier( null )->verify( [
			'receiver_email' => 'foerderpp@wikimedia.de',
			'payment_status' => 'Unknown',
		] ) );
	}

	public function testReassuringReceivedDataSucceeds_verifierReturnsTrue() {
		$verifier = $this->newVerifier( $this->newSucceedingClient() );
		$this->assertTrue( $verifier->verify( [
			'receiver_email' => 'foerderpp@wikimedia.de',
			'payment_status' => 'Completed'
		] ) );
	}

	public function testReassuringReceivedDataFails_verifierReturnsFalse() {
		$verifier = $this->newVerifier( $this->newFailingClient() );
		$this->assertFalse( $verifier->verify( [
			'receiver_email' => 'foerderpp@wikimedia.de',
			'payment_status' => 'Completed'
		] ) );
	}

	private function newVerifier( $client ) {
		return new PayPalPaymentNotificationVerifier(
			$client,
			[
				'base-url' => 'https://dummy-url.com',
				'account-address' => 'foerderpp@wikimedia.de'
			]
		);
	}

	private function newSucceedingClient() {
		$body = $this->getMockBuilder( Stream::class )
			->disableOriginalConstructor()
			->setMethods( [ 'getContents' ] )
			->getMock();

		$body->expects( $this->once() )
			->method( 'getContents' )
			->willReturn( 'VERIFIED' );

		return $this->newClient( $body );
	}

	private function newFailingClient() {
		$body = $this->getMockBuilder( Stream::class )
			->disableOriginalConstructor()
			->setMethods( [ 'getContents' ] )
			->getMock();

		$body->expects( $this->once() )
			->method( 'getContents' )
			->willReturn( 'INVALID' );

		return $this->newClient( $body );
	}

	private function newClient( $body ) {
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
			->with( 'https://dummy-url.com', [
				'cmd' => '_notify_validate',
				'receiver_email' => 'foerderpp@wikimedia.de',
				'payment_status' => 'Completed'
			] )
			->willReturn( $response );

		return $client;
	}

}
