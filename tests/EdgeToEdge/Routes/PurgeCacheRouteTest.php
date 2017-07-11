<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PurgeCacheRouteTest extends WebRouteTestCase {

	const INVALID_SECRET = 'pedo mellon a minno';

	public function testGivenInvalidSecret_responseIsReturned(): void {
		$client = $this->createClient();

		$client->request(
			'GET',
			'/purge-cache',
			[
				'secret' => self::INVALID_SECRET
			]
		);

		$this->assertSame( 200, $client->getResponse()->getStatusCode() );
		$this->assertSame( 'ACCESS DENIED', $client->getResponse()->getContent() );
	}

}
