<?php

declare( strict_types = 1 );

namespace EdgeToEdge;

use PHPUnit\Framework\Attributes\CoversClass;
use WMDE\Fundraising\Frontend\App\EventHandlers\StoreTheme;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes\GetApplicationVarsTrait;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

#[CoversClass( StoreTheme::class )]
class StoreThemeTest extends WebRouteTestCase {

	use GetApplicationVarsTrait;

	/**
	 * Remove when https://phabricator.wikimedia.org/T163452 is done
	 */
	protected function setUp(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
	}

	public function testStoresAllowedTheme(): void {
		$client = $this->createClient();

		$client->request( 'GET', '/actually-every-route?theme=green' );

		$applicationVars = $this->getDataApplicationVars( $client->getCrawler() );

		$this->assertSame( 'green', $applicationVars->theme );
	}

	public function testUsesDefaultWhenThemeNotSpecified(): void {
		$client = $this->createClient();

		$client->request( 'GET', '/actually-every-route' );

		$applicationVars = $this->getDataApplicationVars( $client->getCrawler() );

		$this->assertSame( 'default', $applicationVars->theme );
	}

	public function testUsesDefaultWhenThemeNotAllowed(): void {
		$client = $this->createClient();

		$client->request( 'GET', '/actually-every-route?theme=hotdogstand' );

		$applicationVars = $this->getDataApplicationVars( $client->getCrawler() );

		$this->assertSame( 'default', $applicationVars->theme );
	}
}
