<?php

namespace WMDE\Fundraising\Frontend\Tests\System;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class RouteNotFoundTest extends SystemTestCase {

	public function testGivenUnknownRoute_404isReturned() {
		$client = $this->createClient();
		$client->request( 'GET', '/kittens' );

		$this->assert404( $client->getResponse(), 'No route found for "GET /kittens"' );
	}

}
