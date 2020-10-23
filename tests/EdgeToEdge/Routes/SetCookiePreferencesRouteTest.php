<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Component\BrowserKit\Cookie;
use WMDE\Fundraising\Frontend\App\Controllers\Membership\ApplyForMembershipController;
use WMDE\Fundraising\Frontend\App\Controllers\SetCookiePreferencesController;
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
			'set-cookie-preferences',
			[ CookieNames::CONSENT => 'yes' ]
		);

		$cookieJar = $client->getCookieJar();
		$cookieJar->updateFromResponse( $client->getInternalResponse() );
		$cookie = $cookieJar->get( CookieNames::CONSENT );

		$this->assertSame( $cookie->getValue(), 'yes' );
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

		$cookieJar = $client->getCookieJar();
		$cookieJar->updateFromResponse( $client->getInternalResponse() );
		$cookie = $cookieJar->get( CookieNames::TRACKING );

		$this->assertSame( $cookie->getValue(), 'nicholas/cage' );
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

		$cookieJar = $client->getCookieJar();
		$cookieJar->updateFromResponse( $client->getInternalResponse() );

		$this->assertNull( $cookieJar->get( CookieNames::TRACKING ) );
	}

	public function testWhenCookieConsentRejected_trackingCookiesAreRemoved() {
		$client = $this->createClient();
		$client->getCookieJar()->set( new Cookie( CookieNames::TRACKING, 'nicholas/cage' ) );
		$client->getCookieJar()->set( new Cookie( CookieNames::BUCKET_TESTING, 'set' ) );

		$client->request(
			'POST',
			'set-cookie-preferences',
			[
				CookieNames::CONSENT => 'no'
			]
		);

		$cookieJar = $client->getCookieJar();
		$cookieJar->updateFromResponse( $client->getInternalResponse() );

		$this->assertNull( $cookieJar->get( CookieNames::TRACKING ) );
		$this->assertNull( $cookieJar->get( CookieNames::BUCKET_TESTING ) );
	}
}
