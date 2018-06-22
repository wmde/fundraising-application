<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedCampaignConfigurationLoader;
use WMDE\Fundraising\Frontend\Tests\Fixtures\OverridingCampaignConfigurationLoader;

/**
 * Class CampaignCookieTest
 * @package WMDE\Fundraising\Frontend\Tests\EdgeToEdge
 */
class CampaignCookieTest extends WebRouteTestCase {

	const COOKIE_NAME = 'spenden_ttg';

	const TEST_CAMPAIGN_CONFIG = [
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
			function( FunFunFactory $ffactory, array $config ) {
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
		$this->assertSame( '2099-12-31 00:00:00', date( 'Y-m-d H:i:s', (int) $cookie->getExpiresTime() ) );
	}

	public function testWhenUserVisitsThePageWithUrlParams_cookieIsChanged(): void {
		$client = $this->createClient(
			[],
			function ( FunFunFactory $factory ): void {
				$factory->setCampaignConfigurationLoader( new OverridingCampaignConfigurationLoader(
					$factory->getCampaignConfigurationLoader(),
					[ 'skins' => [ 'active' => true ] ]
				) );
			}
		);
		$client->request( 'get', '/', [] );
		$client->request( 'get', '/', [ 'skin' => 1 ] );
		$this->assertContains( 'skin=1', $client->getCookieJar()->get( self::COOKIE_NAME )->getValue() );
	}

	public function testWhenCampaignsAreInactive_cookieExpiresAtEndOfSession(): void {
		$client = $this->createClient( [ 'campaigns' => [ 'timezone' => 'UTC' ] ],
			function( FunFunFactory $ffactory, array $config ) {
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
