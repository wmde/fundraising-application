<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\BrowserKit\Cookie as BrowserKitCookie;
use WMDE\Fundraising\Frontend\App\CookieNames;
use WMDE\Fundraising\Frontend\App\EventHandlers\StoreLocale;

#[CoversClass( StoreLocale::class )]
class StoreLocaleTest extends WebRouteTestCase {

	/**
	 * Remove when https://phabricator.wikimedia.org/T163452 is done
	 */
	protected function setUp(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
	}

	public function testWhenGivenSupportedCookieLocale_setsLocale(): void {
		$client = $this->createClient();
		$client->getCookieJar()->set( new BrowserKitCookie( CookieNames::LOCALE, 'en_GB' ) );

		$client->request( 'GET', '/actually-every-route' );

		$this->assertSame( 'en_GB', $client->getRequest()->getLocale() );
	}

	public function testWhenGivenUnsupportedCookieLocale_defaultsToGerman(): void {
		$client = $this->createClient();
		$client->getCookieJar()->set( new BrowserKitCookie( CookieNames::LOCALE, 'I AM NOT A VALID LOCALE' ) );

		$client->request( 'GET', '/actually-every-route' );

		$this->assertSame( 'de_DE', $client->getRequest()->getLocale() );
	}

	public function testWhenGivenSupportedUrlLocale_setsLocale(): void {
		$client = $this->createClient();

		$client->request( 'GET', '/actually-every-route?locale=en_GB' );

		$this->assertSame( 'en_GB', $client->getRequest()->getLocale() );
	}

	public function testWhenGivenUnsupportedUrlLocale_defaultsToGerman(): void {
		$client = $this->createClient();

		$client->request( 'GET', '/actually-every-route?locale=I-AM-NOT-A-VALID-LOCALE' );

		$this->assertSame( 'de_DE', $client->getRequest()->getLocale() );
	}

	public function testWhenGivenUrlLocale_andCookieLocaleIsSet_doesNotUpdateLocale(): void {
		$client = $this->createClient();
		$client->getCookieJar()->set( new BrowserKitCookie( CookieNames::LOCALE, 'en_GB' ) );

		$client->request( 'GET', '/actually-every-route?locale=de_DE' );

		$this->assertSame( 'en_GB', $client->getRequest()->getLocale() );
	}

	public function testWhenGivenUrlLocale_andCookieLocaleIsNotSet_createsCookie(): void {
		$client = $this->createClient();

		$client->request( 'GET', '/actually-every-route?locale=en_GB' );

		$this->assertSame( 'en_GB', $client->getCookieJar()->get( CookieNames::LOCALE )?->getValue() );
	}

	public function testWhenGivenInvalidUrlLocale_andCookieLocaleIsNotSet_doesNotCreateCookie(): void {
		$client = $this->createClient();

		$client->request( 'GET', '/actually-every-route?locale=I-AM-NOT-A-VALID-LOCALE' );

		$this->assertNull( $client->getCookieJar()->get( CookieNames::LOCALE ) );
	}
}
