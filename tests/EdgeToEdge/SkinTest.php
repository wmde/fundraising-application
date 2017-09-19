<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;

class SkinTest extends WebRouteTestCase {

	public function testDefaultSkinUsed(): void {
		$client = $this->createClient( $this->getDummyConfig(), null, self::DISABLE_DEBUG );
		$client->request( 'GET', '/' );

		$this->assertContains( '10h16', $client->getResponse()->getContent() );
		$this->assertNoSkinResponseCookie( $client->getResponse() );
	}

	public function testSkinChoosableViaCookie(): void {
		$client = $this->createClient( $this->getDummyConfig(), null, self::DISABLE_DEBUG );
		$client->getCookieJar()->set( new Cookie( 'skin', 'cat17' ) );
		$client->request( 'GET', '/' );

		$this->assertContains( 'cat17', $client->getResponse()->getContent() );
		$this->assertNoSkinResponseCookie( $client->getResponse() );
	}

	public function testSkinChoosableViaQuery(): void {
		$client = $this->createClient( $this->getDummyConfig(), null, self::DISABLE_DEBUG );
		$client->request( 'GET', '/', [ 'skin' => 'cat17' ] );

		$this->assertContains( 'cat17', $client->getResponse()->getContent() );
		$this->assertSkinResponseCookie( 'cat17', $client->getResponse() );
	}

	public function testSkinViaQuerySuperseedsCookie(): void {
		$client = $this->createClient( $this->getDummyConfig(), null, self::DISABLE_DEBUG );
		$client->getCookieJar()->set( new Cookie( 'skin', 'cat17' ) );
		$client->request( 'GET', '/', [ 'skin' => '10h16' ] );

		$this->assertContains( '10h16', $client->getResponse()->getContent() );
		$this->assertSkinResponseCookie( '10h16', $client->getResponse() );
	}

	public function testInvalidQueryIgnored(): void {
		$client = $this->createClient( $this->getDummyConfig(), null, self::DISABLE_DEBUG );
		$client->request( 'GET', '/', [ 'skin' => 'fff' ] );

		$this->assertContains( '10h16', $client->getResponse()->getContent() );
		$this->assertNoSkinResponseCookie( $client->getResponse() );
	}

	public function testInvalidCookieIgnored(): void {
		$client = $this->createClient( $this->getDummyConfig(), null, self::DISABLE_DEBUG );
		$client->getCookieJar()->set( new Cookie( 'skin', 'ggg' ) );
		$client->request( 'GET', '/' );

		$this->assertContains( '10h16', $client->getResponse()->getContent() );
		$this->assertNoSkinResponseCookie( $client->getResponse() );
	}

	private function getDummyConfig(): array {
		return [
			'skin' => [
				'options' => [ '10h16', 'cat17' ],
				'default' => '10h16'
			]
		];
	}

	private function assertSkinResponseCookie( string $expected, Response $response ): void {
		$cookies = $response->headers->getCookies();
		foreach ( $cookies as $cookie ) {
			if ( $cookie->getName() !== 'skin' ) {
				continue;
			}
			$this->assertSame( $expected, $cookie->getValue() );
		}
	}

	private function assertNoSkinResponseCookie( Response $response ): void {
		$cookies = $response->headers->getCookies();
		foreach ( $cookies as $cookie ) {
			if ( $cookie->getName() === 'skin' ) {
				$this->fail( 'Found an unexpected "skin" response cookie.' );
			}
		}
	}
}
