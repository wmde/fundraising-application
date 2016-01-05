<?php

namespace WMDE\Fundraising\Frontend\Tests\System;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ListCommentsRssRouteTest extends SystemTestCase {

	public function testRouteDoesSomething() {
		$client = $this->createClient();
		$client->request( 'GET', '/list.rss' );

		$this->assertSame(
			'TODO',
			$client->getResponse()->getContent()
		);
	}

}
