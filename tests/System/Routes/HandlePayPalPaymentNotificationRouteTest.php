<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\PayPalPaymentNotificationVerifier;
use WMDE\Fundraising\Frontend\Tests\System\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class HandlePayPalPaymentNotificationRouteTest extends WebRouteTestCase {

	const BASE_URL = 'https://that.paymentprovider.com/';
	const EMAIL_ADDRESS = 'foerderpp@wikimedia.de';

	public function testGivenValidRequest_applicationIndicatesSuccess() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setPayPalPaymentNotificationVerifier( $this->newNotifierMock() );

			$client->request(
				'POST',
				'/handle-paypal-payment-notification',
				[
					'receiver_email' => self::EMAIL_ADDRESS,
					'payment_status' => 'Completed'
				]
			);

			$this->assertSame( 'TODO', $client->getResponse()->getContent() );
			$this->assertSame( 200, $client->getResponse()->getStatusCode() );
		} );
	}

	private function newNotifierMock() {
		return new PayPalPaymentNotificationVerifier(
			$this->newGuzzleClientMock(),
			[
				'base-url' => self::BASE_URL,
				'account-address' => self::EMAIL_ADDRESS
			]
		);
	}

	private function newGuzzleClientMock(): GuzzleClient {
		$body = $this->getMockBuilder( Stream::class )
			->disableOriginalConstructor()
			->setMethods( [ 'getContents' ] )
			->getMock();

		$body->expects( $this->any() )
			->method( 'getContents' )
			->willReturn( 'VERIFIED' );

		$response = $this->getMockBuilder( Response::class )
			->disableOriginalConstructor()
			->setMethods( [ 'getBody' ] )
			->getMock();

		$response->expects( $this->any() )
			->method( 'getBody' )
			->willReturn( $body );

		$client = $this->getMockBuilder( GuzzleClient::class )
			->disableOriginalConstructor()
			->setMethods( [ 'post' ] )
			->getMock();

		$client->expects( $this->any() )
			->method( 'post' )
			->with(
				self::BASE_URL,
				[
					'cmd' => '_notify_validate',
					'receiver_email' => self::EMAIL_ADDRESS,
					'payment_status' => 'Completed'
				]
			)
			->willReturn( $response );

		return $client;
	}

	public function testGivenInvalidRequest_applicationReturnsError() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setPayPalPaymentNotificationVerifier( $this->newNotifierMock() );

			$client->request(
				'POST',
				'/handle-paypal-payment-notification',
				[
					'receiver_email' => self::EMAIL_ADDRESS,
					'payment_status' => 'Unknown'
				]
			);

			$this->assertSame( 'TODO', $client->getResponse()->getContent() );
			$this->assertSame( 200, $client->getResponse()->getStatusCode() );
		} );
	}

}
