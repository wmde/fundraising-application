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

	const UPDATE_TOKEN_COOKIE_NAME = 'wmde-fundraising-utoken';
	const DONATION_ID_COOKIE_NAME = 'wmde-fundraising-donation-id';

	public function testWhenCreatingDonation_itCanBeCancelled() {
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
					'sid' => $client->getCookieJar()->get( self::DONATION_ID_COOKIE_NAME )->getValue(),
					'utoken' => $client->getCookieJar()->get( self::UPDATE_TOKEN_COOKIE_NAME )->getValue(),
				]
			);

			$this->assertContains( 'wurde storniert', $client->getResponse()->getContent() );
		} );
	}

}
