<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use Symfony\Component\BrowserKit\Cookie as BrowserKitCookie;
use WMDE\Fundraising\Frontend\App\CookieNames;

/**
 * @covers \WMDE\Fundraising\Frontend\App\EventHandlers\StoreLocale
 */
class StoreLocaleTest extends WebRouteTestCase {

	public function testWhenGivenSupportedLocale_setsLocale(): void {
		$this->markTestSkipped( 'This test needs to be reactivated once we have the English translations' );

		$supportedLocale = 'en_GB';
		$client = $this->createClient();
		$client->getCookieJar()->set( new BrowserKitCookie( CookieNames::LOCALE, $supportedLocale ) );

		$client->request( 'GET', '/actually-every-route' );

		$this->assertSame( $supportedLocale, $client->getRequest()->getLocale() );
	}

	public function testWhenGivenUnsupportedLocale_defaultsToGerman(): void {
		$client = $this->createClient();
		$client->getCookieJar()->set( new BrowserKitCookie( CookieNames::LOCALE, 'I AM NOT A VALID LOCALE' ) );

		$client->request( 'GET', '/actually-every-route' );

		$this->assertSame( 'de_DE', $client->getRequest()->getLocale() );
	}
}
