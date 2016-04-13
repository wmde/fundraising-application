<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

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

	public function testGivenValidRequest_applicationIndicatesSuccess() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setPayPalPaymentNotificationVerifier(
				new PayPalPaymentNotificationVerifier(
					$this->getClientMock(),
					[
						'base-url' => 'https://that.paymentprovider.com/',
						'account-address' => 'foerderpp@wikimedia.de'
					]
				)
			);

			$client->request(
				'POST',
				'/handle-paypal-payment-notification',
				[
					'receiver_email' => 'foerderpp@wikimedia.de',
					'payment_status' => 'Completed'
				]
			);

			$this->assertSame( 'TODO', $client->getResponse()->getContent() );
			$this->assertSame( 200, $client->getResponse()->getStatusCode() );
		} );
	}

	private function getClientMock() {
		$body = $this->getMockBuilder( Stream::class )
			->disableOriginalConstructor()
			->setMethods( [ 'getContents' ] )
			->getMock();

		$body->expects( $this->once() )
			->method( 'getContents' )
			->willReturn( 'VERIFIED' );

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
			->with(
				'https://that.paymentprovider.com/',
				[
					'cmd' => '_notify_validate',
					'receiver_email' => 'foerderpp@wikimedia.de',
					'payment_status' => 'Completed'
				]
			)
			->willReturn( $response );

		return $client;
	}

}
