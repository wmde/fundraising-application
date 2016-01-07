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

		$this->assertSame(
			'<html><header />missing: kittens</html>',
			$client->getResponse()->getContent()
		);
	}

	public function testWhenPageDoesNotExist_pageNameInResponseIsEscaped() {
		$client = $this->createClient();
		$client->request( 'GET', '/page/<script>alert("kittens");' );

		$this->assertSame(
			'<html><header />missing: &lt;script&gt;alert(&quot;kittens&quot;);</html>',
			$client->getResponse()->getContent()
		);
	}

	public function testWhenPageExists_itGetsEmbedded() {
		$this->insertUnicornsPage();

		$client = $this->createClient();
		$client->request( 'GET', '/page/unicorns' );

		$this->assertSame(
			'<html><header />Pink fluffy unicorns dancing on rainbows</html>',
			$client->getResponse()->getContent()
		);
	}

	private function insertUnicornsPage() {
		$this->testEnvironment->getFactory()->setFileFetcher( new InMemoryFileFetcher( [
			'http://cms.wiki/unicorns' => 'Pink fluffy unicorns dancing on rainbows'
		] ) );
	}

	public function testWhenPageNameContainsSlash_404isReturned() {
		$this->insertUnicornsPage();

		$client = $this->createClient();
		$client->request( 'GET', '/page/unicorns/of-doom' );

		$this->assert404( $client->getResponse(), 'No route found for "GET /page/unicorns/of-doom"' );
	}

}
