<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use WMDE\Fundraising\Frontend\FeatureToggle\Feature;
use WMDE\Fundraising\Frontend\Infrastructure\FileFeatureReader;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes\GetApplicationVarsTrait;

/**
 * Check if basic tracking parameters are rendered inside the HTML
 *
 * @covers \WMDE\Fundraising\Frontend\Presentation\ActiveFeatureRenderer
 * @covers \WMDE\Fundraising\Frontend\FeatureToggle\Feature
 */
class FeatureToggleTest extends WebRouteTestCase {

	use GetApplicationVarsTrait;

	public function testActiveFeaturesAreSet(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$featureReader = $this->createMock( FileFeatureReader::class );
		$featureReader->method( 'getFeatures' )->willReturn( [
			new Feature( 'feature_a', true ),
			new Feature( 'feature_b', false ),
		] );
		$client = $this->createClient();
		$factory = $this->getFactory();
		$factory->setFeatureReader( $featureReader );

		$client->request( 'GET', '/' );

		$applicationVars = $this->getDataApplicationVars( $client->getCrawler() );
		$this->assertSame( [ 'features.feature_a' ], $applicationVars->activeFeatures );
	}

}
