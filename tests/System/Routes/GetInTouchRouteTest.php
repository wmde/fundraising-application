<?php

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use WMDE\Fundraising\Frontend\Tests\System\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class GetInTouchRouteTest extends WebRouteTestCase {

	public function testGivenValidRequest_contactRequestIsProperlyProcessed() {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/contact/get-in-touch',
			[] // TODO
		);

		$this->assertContains(
			'TODO',
			$client->getResponse()->getContent()
		);
	}

}
