<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use WMDE\Fundraising\Frontend\Tests\System\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CancelDonationRouteTest extends WebRouteTestCase {

	public function testGivenValidArguments_requestResultsIn200() {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/donation/cancel',
			[
				'sid' => '',
				'token' => '',
				'utoken' => '',
			]
		);

		$this->assertSame( 200, $client->getResponse()->getStatusCode() );
	}

	public function testGivenGetRequest_resultHas405methodNotAllowedStatus() {
		$client = $this->createClient();

		$client->request(
			'GET',
			'/donation/cancel',
			[
				'sid' => '',
				'token' => '',
				'utoken' => '',
			]
		);

		$this->assertSame( 405, $client->getResponse()->getStatusCode() );
	}

}
