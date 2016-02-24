<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\System;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class RouteNotFoundTest extends WebRouteTestCase {

	public function testGivenUnknownRoute_404isReturned() {
		$client = $this->createClient( [], null, self::DISABLE_DEBUG );
		$client->request( 'GET', '/kittens' );

		$this->assert404( $client->getResponse() );
	}

	public function testGivenUnknownRoute_responseIsHTML() {
		$client = $this->createClient( [], null, self::DISABLE_DEBUG );
		$client->request( 'GET', '/kittens' );

		$this->assertContains( 'text/html', $client->getResponse()->headers->get( 'Content-Type' ) );
		$this->assertContains( '<html', $client->getResponse()->getContent() );
	}

	public function testGivenUnknownRouteAndJSONRquest_responseIsJSON() {
		$client = $this->createClient( [], null, self::DISABLE_DEBUG );
		$client->request( 'GET', '/kittens', [], [], ['HTTP_Accept' => 'application/json'] );

		$this->assertJsonResponse( ['ERR' => 'No route found for "GET /kittens"'], $client->getResponse() );
	}

}
