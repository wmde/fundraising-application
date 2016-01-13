<?php

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use WMDE\Fundraising\Frontend\Tests\System\SystemTestCase;

/**
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddSubscriptionRouteTest extends SystemTestCase {

	public function testRouteDoesSomething() {
		$client = $this->createClient();
		$client->request( 'POST', '/contact/subscribe' );

		$this->assertSame(
			'TODO',
			$client->getResponse()->getContent()
		);
	}

}
