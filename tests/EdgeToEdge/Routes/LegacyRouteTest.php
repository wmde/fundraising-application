<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LegacyRouteTest extends WebTestCase {

	/**
	 * Covers the fallback route in routes.yml
	 *
	 * @coversNothing
	 * @dataProvider legacyRouteProvider
	 */
	public function testGetRequestsToLegacyRoutesRedirectToMainPage( string $route ): void {
		$client = static::createClient();

		$client->request(
			'GET',
			$route,
		);

		$response = $client->getResponse();
		$this->assertTrue( $response->isRedirection(), 'Legacy URL should redirect' );
		$location = $response->headers->get( 'Location', '' ) ?? '';
		$path = parse_url( $location, PHP_URL_PATH );
		$this->assertSame( '/', $path );
	}

	/**
	 * @return iterable<array{string}>
	 */
	public static function legacyRouteProvider(): iterable {
		yield [ '/spenden' ];
		yield [ '/spenden/' ];
		yield [ '/spenden/jetzt-spenden' ];
		yield [ '/spenden/spenden/uebersicht' ];
	}

	/**
	 * @covers \WMDE\Fundraising\Frontend\App\Controllers\PageNotFoundController
	 * @dataProvider legacyRouteProvider
	 */
	public function testPostRequestsToLegacyRoutesRedirectToMainPage( string $route ): void {
		$client = static::createClient();

		$client->request(
			'POST',
			$route,
		);

		$response = $client->getResponse();
		$this->assertTrue( $response->isNotFound() );
	}

}
