<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\App\CookieNames;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\HttpKernelBrowser;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\SetCookiePreferencesController
 */
class SetCookiePreferencesRouteTest extends WebRouteTestCase {

	public function testWhenCookieConsentSet_valueIsPersisted(): void {
		$client = $this->createClient();

		$client->request(
			'POST',
			'set-cookie-preferences',
			[ CookieNames::CONSENT => 'yes' ]
		);

		$this->assertCookieHasValue( $client, CookieNames::CONSENT, 'yes' );
	}

	/**
	 * @param HttpKernelBrowser $client
	 * @param string $name
	 * @param mixed $expectedValue
	 */
	private function assertCookieHasValue( HttpKernelBrowser $client, string $name, $expectedValue ) {
		$cookieJar = $client->getCookieJar();
		$cookieJar->updateFromResponse( $client->getInternalResponse() );
		$cookie = $cookieJar->get( $name );
		$this->assertSame( $cookie->getValue(), $expectedValue );
	}

	public function testWhenCookieConsentSetAndTrackingPassed_trackingValueIsPersisted(): void {
		$client = $this->createClient();

		$client->request(
			'POST',
			'set-cookie-preferences',
			[
				CookieNames::CONSENT => 'yes',
				'piwik_campaign' => 'nicholas',
				'piwik_kwd' => 'cage',
			]
		);

		$this->assertCookieHasValue( $client, CookieNames::TRACKING, 'nicholas/cage' );
	}

	public function testWhenCookieConsentNotSetAndTrackingPassed_trackingValueIsNotPersisted(): void {
		$client = $this->createClient();

		$client->request(
			'POST',
			'set-cookie-preferences',
			[
				CookieNames::CONSENT => 'no',
				'piwik_campaign' => 'Nicholas',
				'piwik_kwd' => 'Cage',
			]
		);

		$this->assertCookieIsNotSet( $client, CookieNames::TRACKING );
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

	public function testWhenCookieConsentRejected_trackingCookiesAreRemoved() {
		$client = $this->createClient( [], null, [
			CookieNames::TRACKING => 'nicholas/cage',
			CookieNames::BUCKET_TESTING => 'set',
		] );

		$client->request(
			'POST',
			'set-cookie-preferences',
			[
				CookieNames::CONSENT => 'no'
			]
		);

		$this->assertCookieIsNotSet( $client, CookieNames::TRACKING );
		$this->assertCookieIsNotSet( $client, CookieNames::BUCKET_TESTING );
	}
}
