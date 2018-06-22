<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\Fixtures\OverridingCampaignConfigurationLoader;

/**
 * Class CampaignCookieTest
 * @package WMDE\Fundraising\Frontend\Tests\EdgeToEdge
 */
class CampaignCookieTest extends WebRouteTestCase {

	const COOKIE_NAME = 'spenden_ttg';

	public function testWhenUserVisitsThePage_cookieIsSet(): void {
		$client = $this->createClient();
		$client->request( 'get', '/', [] );
		$this->assertNotEmpty( $client->getCookieJar()->get( self::COOKIE_NAME )->getValue() );
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

}
