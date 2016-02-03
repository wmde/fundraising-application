<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\System;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class RouteNotFoundTest extends WebRouteTestCase {

	public function testGivenUnknownRoute_404isReturned() {
		$client = $this->createClient();
		$client->request( 'GET', '/kittens' );

		$this->assert404( $client->getResponse() );
	}

}
