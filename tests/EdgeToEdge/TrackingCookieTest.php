<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use Symfony\Component\BrowserKit\Cookie;
use WMDE\Fundraising\Frontend\App\Controllers\SetCookiePreferencesController;
use WMDE\Fundraising\Frontend\App\CookieNames;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Bootstrap
 * @covers \WMDE\Fundraising\Frontend\App\EventHandlers\RegisterTrackingData
 */
class TrackingCookieTest extends WebRouteTestCase {

	private const PARAM_NAME_CAMPAIGN = 'piwik_campaign';
	private const PARAM_NAME_KEYWORD = 'piwik_kwd';

	public function testWhenTrackingParamsArePassed_valuesAreStoredInCookie(): void {
		$client = $this->createClient( [], null, [ CookieNames::CONSENT => 'yes' ] );
		$client->request( 'get', '/', [
			self::PARAM_NAME_CAMPAIGN => 'campaign',
			self::PARAM_NAME_KEYWORD => 'keyword'
		] );

		$this->assertSame( 'campaign/keyword', $client->getCookieJar()->get( CookieNames::TRACKING )->getValue() );
	}

	public function testWhenTrackingParamsAreNotPassed_noCookieIsCreated(): void {
		$client = $this->createClient();
		$client->request( 'get', '/', [] );

		$this->assertNull( $client->getCookieJar()->get( CookieNames::TRACKING ) );
	}

	public function testWhenEmptyTrackingParamsArePassed_noCookieIsCreated(): void {
		$client = $this->createClient();
		$client->request( 'get', '/', [
			self::PARAM_NAME_CAMPAIGN => '',
			self::PARAM_NAME_KEYWORD => ''
		] );

		$this->assertNull( $client->getCookieJar()->get( CookieNames::TRACKING ) );
	}

	public function testWhenNewValuesAreProvided_theOldOnesAreKept(): void {
		$client = $this->createClient();

		$client->getCookieJar()->set( new Cookie(
			CookieNames::TRACKING,
			'leeroy jenkins'
		) );

		$client->request( 'get', '/', [
			self::PARAM_NAME_CAMPAIGN => 'campaign',
			self::PARAM_NAME_KEYWORD => 'keyword'
		] );

		$this->assertSame( 'leeroy jenkins', $client->getCookieJar()->get( CookieNames::TRACKING )->getValue() );
	}

}
