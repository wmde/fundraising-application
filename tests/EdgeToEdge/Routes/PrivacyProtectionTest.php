<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * Fundraising privacy page test
 */
class PrivacyProtectionTest extends WebRouteTestCase {

	public function testWhenPrivacyProtectionPageIsRendered_optOutFormIsDisplayed(): void {
		$client = $this->createClient( [ 'skin' => [ 'default' => 'cat17' ] ] );
		$client->request(
			'GET',
			'/page/Datenschutz'
		);
		$crawler = $client->getCrawler();

		$this->assertSame( 1, $crawler->filter( '.privacy_wrapper' )->count() );

	}
}
