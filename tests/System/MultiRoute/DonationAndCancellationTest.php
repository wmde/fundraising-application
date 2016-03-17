<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\System\MultiRoute;

use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\System\Routes\AddDonationRouteTest;
use WMDE\Fundraising\Frontend\Tests\System\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DonationAndCancellationTest extends WebRouteTestCase {

	public function testWhenCreatingDonation_itCanBeCancelled() {
		self::markTestIncomplete( 'We cannot currently retrieve the token and donation id from the response' );

		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();

			$client->request(
				'POST',
				'/donation/add',
				AddDonationRouteTest::newValidFormInput()
			);

			$this->assertSame( 200, $client->getResponse()->getStatusCode() );

			$client->request(
				'POST',
				'/donation/cancel',
				[
					'sid' => 'TODO',
					'utoken' => 'TODO',
				]
			);

			$this->assertContains( 'wurde storniert', $client->getResponse()->getContent() );
		} );
	}

}
