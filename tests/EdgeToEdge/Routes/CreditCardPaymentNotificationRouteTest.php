<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class CreditCardPaymentNotificationRouteTest extends WebRouteTestCase {

	public function testRouteExists() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$client->request(
				'POST',
				'/handle-creditcard-payment-notification',
				[]
			);

			$this->assertSame( 200, $client->getResponse()->getStatusCode() );
			$this->assertContains( 'TODO', $client->getResponse()->getContent() );
		} );
	}

}
