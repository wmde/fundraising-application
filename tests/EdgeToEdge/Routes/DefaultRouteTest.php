<?php
declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use PHPUnit\Framework\Attributes\CoversClass;
use WMDE\Fundraising\Frontend\App\Controllers\Donation\NewDonationController;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

#[CoversClass( NewDonationController::class )]
class DefaultRouteTest extends WebRouteTestCase {

	public function testItRendersTheDonationForm(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();

		$client->request( 'GET', '/' );

		$this->assertMatchesRegularExpression(
			'/<script src="[^"]*js\/donation_form\.js/',
			$client->getResponse()->getContent() ?: ''
		);
	}

}
