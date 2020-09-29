<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Bootstrap
 * @covers \WMDE\Fundraising\Frontend\App\EventHandlers\AddIndicatorAttributeForJsonRequests
 */
class RouteNotFoundTest extends WebRouteTestCase {

	public function testGivenUnknownRoute_404isReturned(): void {
		$client = $this->createClient( [], null, self::DISABLE_DEBUG );
		$client->request( 'GET', '/kittens' );

		$this->assert404( $client->getResponse() );
	}

	public function testGivenUnknownRoute_responseIsHTML(): void {
		$client = $this->createClient( [], null, self::DISABLE_DEBUG );
		$client->request( 'GET', '/kittens' );

		$this->assertStringContainsString( 'text/html', $client->getResponse()->headers->get( 'Content-Type' ) );
		$this->assertStringContainsString( '<html', $client->getResponse()->getContent() );
	}

	public function testGivenUnknownRouteAndJsonRequest_responseIsJSON(): void {
		$client = $this->createClient( [], null, self::DISABLE_DEBUG );
		$client->request( 'GET', '/kittens', [], [], [ 'HTTP_Accept' => 'application/json' ] );

		$this->assertJsonResponse( [ 'ERR' => 'No route found for "GET /kittens"' ], $client->getResponse() );
	}

}
