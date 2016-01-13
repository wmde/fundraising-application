<?php

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use FileFetcher\InMemoryFileFetcher;
use WMDE\Fundraising\Frontend\Tests\System\SystemTestCase;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DisplayPageRouteTest extends SystemTestCase {

	public function testWhenPageDoesNotExist_missingResponseIsReturned() {
		$client = $this->createClient();
		$client->request( 'GET', '/page/kittens' );

		$this->assertContains(
			'missing: Kittens',
			$client->getResponse()->getContent()
		);
	}

	public function testWhenPageDoesNotExist_pageNameInResponseIsEscaped() {
		$client = $this->createClient();
		$client->request( 'GET', '/page/<script>alert("kittens");' );

		$this->assertContains(
			'missing: &lt;script&gt;alert(&quot;kittens&quot;);',
			$client->getResponse()->getContent()
		);
	}

	public function testWhenPageExists_itGetsEmbedded() {
		$this->insertUnicornsPage();

		$client = $this->createClient();
		$client->request( 'GET', '/page/unicorns' );

		$this->assertContains(
			'Pink fluffy unicorns dancing on rainbows',
			$client->getResponse()->getContent()
		);
	}

	private function insertUnicornsPage() {
		$this->testEnvironment->getFactory()->setFileFetcher( new InMemoryFileFetcher( [
			'http://cms.wiki/?title=Unicorns&action=render' => 'Pink fluffy unicorns dancing on rainbows'
		] ) );
	}

	public function testWhenPageNameContainsSlash_404isReturned() {
		$this->insertUnicornsPage();

		$client = $this->createClient();
		$client->request( 'GET', '/page/unicorns/of-doom' );

		$this->assert404( $client->getResponse(), 'No route found for "GET /page/unicorns/of-doom"' );
	}

	public function testWhenNoSubFooter_subFooterDivIsNotShown() {
		// TODO: setup
		$client = $this->createClient();
		$client->request( 'GET', '/page/unicorns' );

		$this->assertNotContains(
			'<div id="subfooter">',
			$client->getResponse()->getContent()
		);
	}

	public function testWhenIsMobile_isMobileJsVarGetsSetToTrue() {
		// TODO: setup
		$client = $this->createClient();
		$client->request( 'GET', '/page/unicorns' );

		$this->assertNotContains(
			'var isMobile = true;',
			$client->getResponse()->getContent()
		);
	}

	public function testFooterAndHeaderGetEmbedded() {
		$client = $this->createClient();
		$client->request( 'GET', '/page/kittens' );

		$this->assertContains(
			'missing: 10hoch16/Seitenkopf',
			$client->getResponse()->getContent()
		);

		$this->assertContains(
			'missing: 10hoch16/Seitenfuß',
			$client->getResponse()->getContent()
		);
	}

}
