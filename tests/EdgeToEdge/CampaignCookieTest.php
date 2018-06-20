<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

class CampaignCookieTest extends WebRouteTestCase {

	const COOKIE_NAME = 'spenden_ttg';

	public function testWhenUserVisitsThePage_cookieIsSet(): void {
		$client = $this->createClient();
		$client->request( 'get', '/', [] );
		$this->assertNotEmpty( $client->getCookieJar()->get( self::COOKIE_NAME )->getValue() );
	}

	public function testWhenUserVisitsThePageWithUrlParams_cookieIsChanged(): void {
		$client = $this->createClient();
		$client->request( 'get', '/', [] );
		$client->request( 'get', '/', [ "cp" => 1 ] );
		$this->assertSame( 'cp=1', $client->getCookieJar()->get( self::COOKIE_NAME )->getValue() );
	}

}
