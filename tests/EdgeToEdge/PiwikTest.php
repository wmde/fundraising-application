<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

/**
 * Check if basic tracking parameters are rendered inside the HTML
 *
 * @coversNothing
 */
class PiwikTest extends WebRouteTestCase {

	public function testPiwikScriptGetsEmbedded(): void {
		$client = $this->createClient();
		$client->request( 'GET', '/' );
		$this->assertStringContainsString( '<!-- Piwik -->', $client->getResponse()->getContent() );
	}

	public function testConfigParametersAreUsed(): void {
		$client = $this->createClient();
		$client->request( 'GET', '/' );

		$this->assertStringContainsString( 'tracking.wikimedia.de', $client->getResponse()->getContent() );
		$this->assertStringContainsString( 'idsite=1234', $client->getResponse()->getContent() );
	}

}
