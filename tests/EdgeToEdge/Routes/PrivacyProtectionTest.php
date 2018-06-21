<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\OverridingCampaignConfigurationLoader;

/**
 * Fundraising privacy page test
 */
class PrivacyProtectionTest extends WebRouteTestCase {

	public function testWhenPrivacyProtectionPageIsRendered_optOutFormIsDisplayed(): void {
		$client = $this->createClient(
			[],
			function ( FunFunFactory $factory ): void {
				$factory->setCampaignConfigurationLoader(
					new OverridingCampaignConfigurationLoader(
						$factory->getCampaignConfigurationLoader(),
						[ 'skins' => [ 'default_bucket' => 'cat17' ] ]
					)
				);
			}
		);
		$client->request(
			'GET',
			'/page/Datenschutz'
		);
		$crawler = $client->getCrawler();

		$this->assertSame( 1, $crawler->filter( '.privacy_wrapper' )->count() );

	}
}
