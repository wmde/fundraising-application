<?php

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use WMDE\Fundraising\Frontend\Tests\System\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ListCommentsJsonRouteTest extends WebRouteTestCase {

	public function testRouteDoesSomething() {
		$client = $this->createClient();
		$client->request( 'GET', '/list-comments.json' );

		$this->assertSame(
			'TODO',
			$client->getResponse()->getContent()
		);
	}

}
