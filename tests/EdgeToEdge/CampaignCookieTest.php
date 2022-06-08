<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use Symfony\Component\BrowserKit\Cookie as BrowserKitCookie;
use Symfony\Component\HttpFoundation\Cookie;
use WMDE\Fundraising\Frontend\App\CookieNames;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\Fixtures\OverridingCampaignConfigurationLoader;

/**
 * @covers \WMDE\Fundraising\Frontend\App\EventHandlers\StoreBucketSelection
 */
class CampaignCookieTest extends WebRouteTestCase {

	private const TEST_CAMPAIGN_CONFIG = [
		'awesome_feature' => [
			'url_key' => 'omg',
			'start' => '2018-01-01',
			'end' => '2099-12-31',
			'active' => true,
			'buckets' => [ 'boring_default', 'awesome_test' ],
			'default_bucket' => 'boring_default'
		]
	];

	public function testWhenUserVisitsThePage_cookieIsSet(): void {
		$this->markTestIncomplete( "This will be gone when we merge the removal PR" );

		$this->modifyConfiguration( [ 'campaigns' => [ 'timezone' => 'UTC' ] ] );
		$this->modifyEnvironment( static function ( FunFunFactory $ffactory ) {
			$ffactory->setCampaignConfigurationLoader(
				new OverridingCampaignConfigurationLoader(
					$ffactory->getCampaignConfigurationLoader(),
					self::TEST_CAMPAIGN_CONFIG
				)
			);
		} );
		$client = $this->createClient();
		$client->getCookieJar()->set( new BrowserKitCookie( CookieNames::CONSENT, 'yes' ) );
		$client->request( 'get', '/', [] );

		$cookie = $client->getCookieJar()->get( CookieNames::BUCKET_TESTING );
		$this->assertNotEmpty( $cookie->getValue() );
		$this->assertFalse( $cookie->isExpired() );
		$this->assertSame( '2099-12-31 00:00:00', date( 'Y-m-d H:i:s', (int)$cookie->getExpiresTime() ) );
	}

	public function testWhenUserVisitsThePageWithUrlParams_cookieIsChanged(): void {
		$this->markTestIncomplete( "This will be gone when we merge the removal PR" );

		$this->modifyEnvironment( static function ( FunFunFactory $ffactory ) {
			$ffactory->setCampaignConfigurationLoader(
				new OverridingCampaignConfigurationLoader(
					$ffactory->getCampaignConfigurationLoader(),
					self::TEST_CAMPAIGN_CONFIG
				)
			);
		} );
		$client = $this->createClient();
		$client->getCookieJar()->set( new BrowserKitCookie( CookieNames::BUCKET_TESTING, 'omg=0' ) );
		$client->getCookieJar()->set( new BrowserKitCookie( CookieNames::CONSENT, 'yes' ) );
		$client->request( 'get', '/', [ 'omg' => 1 ] );
		$responseCookies = $client->getResponse()->headers->getCookies();
		$bucketCookie = array_values( array_filter( $responseCookies, static function ( Cookie $cookie ): bool {
			return $cookie->getName() === CookieNames::BUCKET_TESTING;
		} ) )[0];
		$this->assertStringContainsString( 'omg=1', $bucketCookie->getValue() );
	}

	public function testWhenCampaignsAreInactive_cookieExpiresAtEndOfSession(): void {
		$this->markTestIncomplete( "This will be gone when we merge the removal PR" );

		$this->modifyConfiguration( [ 'campaigns' => [ 'timezone' => 'UTC' ] ] );
		$this->modifyEnvironment( static function ( FunFunFactory $ffactory ) {
			$ffactory->setCampaignConfigurationLoader(
				new OverridingCampaignConfigurationLoader(
					$ffactory->getCampaignConfigurationLoader(),
					[],
					static function ( $campaigns ): array {
						foreach ( $campaigns as $name => $campaign ) {
							$campaigns[$name]['active'] = false;
						}
						return $campaigns;
					}
				)
			);
		} );
		$client = $this->createClient();
		$client->getCookieJar()->set( new BrowserKitCookie( CookieNames::CONSENT, 'yes' ) );
		$client->request( 'get', '/', [] );

		$cookie = $client->getCookieJar()->get( CookieNames::BUCKET_TESTING );
		$this->assertNotEmpty( $cookie->getValue() );
		$this->assertFalse( $cookie->isExpired() );
		// 'null' is the value to set for indicating a session cookie
		$this->assertNull( $client->getCookieJar()->get( CookieNames::BUCKET_TESTING )->getExpiresTime() );
	}

	public function testWhenUserVisitsThePageWithoutConsentCookie_cookieIsNotSet(): void {
		$this->markTestIncomplete( "This will be gone when we merge the removal PR" );

		$this->modifyConfiguration( [ 'campaigns' => [ 'timezone' => 'UTC' ] ] );
		$this->modifyEnvironment( static function ( FunFunFactory $ffactory ) {
			$ffactory->setCampaignConfigurationLoader(
				new OverridingCampaignConfigurationLoader(
					$ffactory->getCampaignConfigurationLoader(),
					self::TEST_CAMPAIGN_CONFIG
				)
			);
		} );
		$client = $this->createClient();
		$client->request( 'get', '/', [] );

		$this->assertNull( $client->getCookieJar()->get( CookieNames::BUCKET_TESTING ) );
	}

}
