<?php

namespace WMDE\Fundraising\Frontend\Tests\System;

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

}
