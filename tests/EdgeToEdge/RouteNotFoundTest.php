<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use PHPUnit\Framework\Attributes\CoversClass;
use WMDE\Fundraising\Frontend\App\EventHandlers\AddIndicatorAttributeForJsonRequests;
use WMDE\Fundraising\Frontend\App\EventHandlers\HandleExceptions;

#[CoversClass( AddIndicatorAttributeForJsonRequests::class )]
#[CoversClass( HandleExceptions::class )]
class RouteNotFoundTest extends WebRouteTestCase {

	public function testGivenUnknownRoute_404isReturned(): void {
		$client = $this->createClient();
		$client->request( 'GET', '/kittens' );

		$this->assert404( $client->getResponse() );
	}

	public function testGivenUnknownRoute_responseIsHTML(): void {
		$client = $this->createClient();
		$client->request( 'GET', '/kittens' );

		$this->assertStringContainsString( 'text/html', $client->getResponse()->headers->get( 'Content-Type' ) ?: '' );
		$this->assertStringContainsString( '<html', $client->getResponse()->getContent() ?: '' );
	}

	public function testGivenUnknownRouteAndJsonRequest_responseIsJSON(): void {
		$client = $this->createClient();
		$client->request( 'GET', '/kittens', [], [], [ 'HTTP_ACCEPT' => 'application/json' ] );

		$response = json_decode( $client->getResponse()->getContent() ?: '', true );
		$this->assertIsArray( $response );
		$this->assertArrayHasKey( 'ERR', $response );
		$this->assertStringContainsString( '/kittens', $response['ERR'] );
	}

}
