<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use Symfony\Component\BrowserKit\Cookie;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class TrackingCookieTest extends WebRouteTestCase {
	
	const PARAM_NAME_CAMPAIGN = 'piwik_campaign';
	const PARAM_NAME_KEYWORD = 'piwik_kwd';

	const COOKIE_NAME = 'spenden_tracking';

	public function testWhenTrackingParamsArePassed_valuesAreStoredInCookie() {
		$client = $this->createClient();
		$client->request( 'get', '/', [
			self::PARAM_NAME_CAMPAIGN => 'campaign',
			self::PARAM_NAME_KEYWORD => 'keyword'
		] );

		$this->assertSame( 'campaign/keyword', $client->getCookieJar()->get( self::COOKIE_NAME )->getValue() );
	}

	public function testWhenTrackingParamsAreNotPassed_noCookieIsCreated() {
		$client = $this->createClient();
		$client->request( 'get', '/', [] );

		$this->assertNull( $client->getCookieJar()->get( self::COOKIE_NAME ) );
	}

	public function testWhenEmptyTrackingParamsArePassed_noCookieIsCreated() {
		$client = $this->createClient();
		$client->request( 'get', '/', [
			self::PARAM_NAME_CAMPAIGN => '',
			self::PARAM_NAME_KEYWORD => ''
		] );

		$this->assertNull( $client->getCookieJar()->get( self::COOKIE_NAME ) );
	}

	public function testNewValuesAreProvided_theOldOnesAreKept() {
		$client = $this->createClient();
		
		$client->getCookieJar()->set( new Cookie(
			self::COOKIE_NAME,
			'leeroy jenkins'
		) );

		$client->request( 'get', '/', [
			self::PARAM_NAME_CAMPAIGN => 'campaign',
			self::PARAM_NAME_KEYWORD => 'keyword'
		] );

		$this->assertSame( 'leeroy jenkins', $client->getCookieJar()->get( self::COOKIE_NAME )->getValue() );
	}

}
