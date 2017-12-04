<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PiwikTest extends WebRouteTestCase {

	const PIWIK_SITE_ID = 1;

	public function testPiwikScriptGetsEmbedded(): void {
		$client = $this->createClient( [], null, self::ENABLE_DEBUG );
		$client->request( 'GET', '/' );
		$this->assertContains( '<!-- Piwik -->', $client->getResponse()->getContent() );
	}

	public function testConfigParametersAreUsed(): void {
		$client = $this->createClient( [], null, self::ENABLE_DEBUG );
		$client->request( 'GET', '/' );

		$this->assertContains( 'tracking.wikimedia.de', $client->getResponse()->getContent() );
		$this->assertContains( 'idsite=1234', $client->getResponse()->getContent() );
	}

}
