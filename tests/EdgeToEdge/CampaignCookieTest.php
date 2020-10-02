<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use Symfony\Component\BrowserKit\Cookie as BrowserKitCookie;
use Symfony\Component\HttpFoundation\Cookie;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\Fixtures\OverridingCampaignConfigurationLoader;

/**
 * @covers \WMDE\Fundraising\Frontend\App\EventHandlers\StoreBucketSelection
 */
class CampaignCookieTest extends WebRouteTestCase {

	private const COOKIE_NAME = 'spenden_ttg';

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
		$client = $this->createClient(
			[ 'campaigns' => [ 'timezone' => 'UTC' ] ],
			function ( FunFunFactory $ffactory, array $config ) {
			$ffactory->setCampaignConfigurationLoader(
				new OverridingCampaignConfigurationLoader(
					$ffactory->getCampaignConfigurationLoader(),
					self::TEST_CAMPAIGN_CONFIG
				)
			);
			}
		);
		$client->request( 'get', '/', [] );

		$cookie = $client->getCookieJar()->get( self::COOKIE_NAME );
		$this->assertNotEmpty( $cookie->getValue() );
		$this->assertFalse( $cookie->isExpired() );
		$this->assertSame( '2099-12-31 00:00:00', date( 'Y-m-d H:i:s', (int)$cookie->getExpiresTime() ) );
	}

	public function testWhenUserVisitsThePageWithUrlParams_cookieIsChanged(): void {
		$client = $this->createClient(
			[],
			function ( FunFunFactory $factory ): void {
				$factory->setCampaignConfigurationLoader( new OverridingCampaignConfigurationLoader(
					$factory->getCampaignConfigurationLoader(),
					self::TEST_CAMPAIGN_CONFIG
				) );
			}
		);
		$client->getCookieJar()->set( new BrowserKitCookie( self::COOKIE_NAME, 'omg=0' ) );
		$client->request( 'get', '/', [ 'omg' => 1 ] );
		$responseCookies = $client->getResponse()->headers->getCookies();
		$bucketCookie = array_values( array_filter( $responseCookies, function ( Cookie $cookie ): bool {
			return $cookie->getName() === self::COOKIE_NAME;
		} ) )[0];
		$this->assertStringContainsString( 'omg=1', $bucketCookie->getValue() );
	}

	public function testWhenCampaignsAreInactive_cookieExpiresAtEndOfSession(): void {
		$client = $this->createClient( [ 'campaigns' => [ 'timezone' => 'UTC' ] ],
			function ( FunFunFactory $ffactory, array $config ) {
				$ffactory->setCampaignConfigurationLoader(
					new OverridingCampaignConfigurationLoader(
						$ffactory->getCampaignConfigurationLoader(),
						[],
						function ( $campaigns ): array {
							foreach ( $campaigns as $name => $campaign ) {
								$campaigns[$name]['active'] = false;
							}
							return $campaigns;
						}
					)
				);
			} );
		$client->request( 'get', '/', [] );

		$cookie = $client->getCookieJar()->get( self::COOKIE_NAME );
		$this->assertNotEmpty( $cookie->getValue() );
		$this->assertFalse( $cookie->isExpired() );
		// 'null' is the value to set for indicating a session cookie
		$this->assertNull( $client->getCookieJar()->get( self::COOKIE_NAME )->getExpiresTime() );
	}

}
