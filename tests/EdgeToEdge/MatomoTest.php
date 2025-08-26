<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * Check if basic tracking parameters are rendered inside the HTML
 */
#[CoversNothing]
class MatomoTest extends WebRouteTestCase {

	/**
	 * Remove when https://phabricator.wikimedia.org/T163452 is done
	 */
	protected function setUp(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
	}

	public function testMatomoScriptGetsEmbedded(): void {
		$client = $this->createClient();
		$client->request( 'GET', '/' );
		$this->assertStringContainsString( '<!-- Matomo -->', $client->getResponse()->getContent() ?: '' );
	}

	public function testConfigParametersAreUsed(): void {
		$client = $this->createClient();
		$client->request( 'GET', '/' );

		$this->assertStringContainsString( 'tracking.wikimedia.de', $client->getResponse()->getContent() ?: '' );
		$this->assertStringContainsString( 'idsite=1234', $client->getResponse()->getContent() ?: '' );
	}

}
