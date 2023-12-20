<?php
declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Donation\NewDonationController
 */
class DefaultRouteTest extends WebRouteTestCase {

	public function testItRendersTheDonationForm(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();

		$client->request( 'GET', '/' );

		$this->assertMatchesRegularExpression(
			'/<script src="[^"]*js\/donation_form\.js/',
			$client->getResponse()->getContent()
		);
	}

}
