<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use PHPUnit\Framework\Attributes\CoversClass;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes\GetApplicationVarsTrait;

/**
 * Check if basic tracking parameters are rendered inside the HTML
 */
#[CoversClass( FunFunFactory::class )]
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
