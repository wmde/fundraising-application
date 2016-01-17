<?php

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use WMDE\Fundraising\Frontend\Tests\System\SystemTestCase;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ListCommentsJsonRouteTest extends SystemTestCase {

	public function testRouteDoesSomething() {
		$client = $this->createClient();
		$client->request( 'GET', '/list-comments.json' );

		$this->assertSame(
			'TODO',
			$client->getResponse()->getContent()
		);
	}

}
