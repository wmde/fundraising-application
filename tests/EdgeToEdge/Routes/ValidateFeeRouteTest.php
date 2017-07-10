<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ValidateFeeRouteTest extends WebRouteTestCase {

	public function testGivenValidParameters_successResponseIsReturned(): void {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/validate-fee',
			[ 'amount' => '12,34', 'paymentIntervalInMonths' => '6', 'addressType' => 'person' ]
		);

		$this->assertJsonSuccessResponse(
			[ 'status' => 'OK' ],
			$client->getResponse()
		);
	}

	public function testGivenInvalidParameters_failureResponseIsReturned(): void {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/validate-fee',
			[ 'amount' => '12,34', 'paymentIntervalInMonths' => '6', 'addressType' => 'firma' ]
		);

		$response = $client->getResponse();
		$this->assertErrorJsonResponse( $response );
		$this->assertSame( $this->newErrorResponse(), json_decode( $response->getContent(), true ) );
	}

	private function newErrorResponse() {
		return [
			'status' => 'ERR',
			'messages' => [
				'amount' => 'too-low'
			]
		];
	}

}
