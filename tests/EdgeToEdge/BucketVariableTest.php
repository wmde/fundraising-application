<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes\GetApplicationVarsTrait;

/**
 * Check if basic tracking parameters are rendered inside the HTML
 *
 * @covers \WMDE\Fundraising\Frontend\Factories\FunFunFactory::getDefaultTwigVariables
 */
class BucketVariableTest extends WebRouteTestCase {

	use GetApplicationVarsTrait;

	public function testActiveFeaturesAreSet(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();

		$client->request( 'GET', '/' );

		$applicationVars = $this->getDataApplicationVars( $client->getCrawler() );
		$this->assertContains( 'campaigns.show_unicorns.default', $applicationVars->selectedBuckets );
		$this->assertContains( 'pfu', $applicationVars->allowedCampaignParameters );
	}

}
