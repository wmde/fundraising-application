<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpKernel\HttpKernelBrowser;
use WMDE\Fundraising\Frontend\App\CookieNames;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\SetCookiePreferencesController
 */
class SetCookiePreferencesRouteTest extends WebRouteTestCase {

	public function testWhenCookieConsentSet_valueIsPersisted(): void {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/set-cookie-preferences',
			[ CookieNames::CONSENT => 'yes' ]
		);

		$this->assertResponseCookieValueSame( CookieNames::CONSENT, 'yes' );
	}

	public function testWhenCookieConsentSetAndTrackingPassed_trackingValueIsPersisted(): void {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/set-cookie-preferences',
			[
				CookieNames::CONSENT => 'yes',
				'piwik_campaign' => 'nicholas',
				'piwik_kwd' => 'cage',
			]
		);

		$this->assertResponseCookieValueSame( CookieNames::TRACKING, 'nicholas/cage' );
	}

	public function testWhenCookieConsentNotSetAndTrackingPassed_trackingValueIsNotPersisted(): void {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/set-cookie-preferences',
			[
				CookieNames::CONSENT => 'no',
				'piwik_campaign' => 'Nicholas',
				'piwik_kwd' => 'Cage',
			]
		);

		$this->assertCookieIsNotSet( $client, CookieNames::TRACKING );
	}

	public function testWhenCookieConsentSet_bucketSelectionIsPersisted(): void {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/set-cookie-preferences',
			[
				CookieNames::CONSENT => 'yes'
			]
		);

		$this->assertNotNull( $client->getCookieJar()->get( CookieNames::BUCKET_TESTING ) );
	}

	public function testWhenCookieConsentRejected_trackingCookiesAreRemoved() {
		$client = $this->createClient();
		$client->getCookieJar()->set( new Cookie( CookieNames::TRACKING, 'nicholas/cage' ) );
		$client->getCookieJar()->set( new Cookie( CookieNames::BUCKET_TESTING, 'set' ) );

		$client->request(
			'POST',
			'/set-cookie-preferences',
			[
				CookieNames::CONSENT => 'no'
			]
		);

		$this->assertCookieIsNotSet( $client, CookieNames::TRACKING );
		$this->assertCookieIsNotSet( $client, CookieNames::BUCKET_TESTING );
	}

	public function testWhenCookieConsentIsRemoved_trackingCookiesAreRemoved(): void {
		$client = $this->createClient();
		$client->getCookieJar()->set( new Cookie( CookieNames::CONSENT, 'yes' ) );
		$client->getCookieJar()->set( new Cookie( CookieNames::TRACKING, 'nicholas/cage' ) );
		$client->getCookieJar()->set( new Cookie( CookieNames::BUCKET_TESTING, 'set' ) );

		$client->request(
			'POST',
			'/set-cookie-preferences',
			[
				CookieNames::CONSENT => 'no'
			]
		);

		$this->assertCookieIsNotSet( $client, CookieNames::TRACKING );
		$this->assertCookieIsNotSet( $client, CookieNames::BUCKET_TESTING );
	}

	/**
	 * @param HttpKernelBrowser $client
	 * @param string $name
	 */
	private function assertCookieIsNotSet( HttpKernelBrowser $client, string $name ) {
		$cookieJar = $client->getCookieJar();
		$cookieJar->updateFromResponse( $client->getInternalResponse() );
		$this->assertNull( $cookieJar->get( $name ) );
	}
}
