<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @license GPL-2.0-or-later
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ValidateFeeRouteTest extends WebRouteTestCase {

	public function testGivenValidParameters_successResponseIsReturned(): void {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/validate-fee',
			[ 'membershipFee' => '1234', 'paymentIntervalInMonths' => '6', 'addressType' => 'person' ]
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
			[ 'membershipFee' => '1234', 'paymentIntervalInMonths' => '6', 'addressType' => 'firma' ]
		);

		$response = $client->getResponse();
		$this->assertErrorJsonResponse( $response );
		$this->assertSame( $this->newErrorResponse(), json_decode( $response->getContent(), true ) );
	}

	private function newErrorResponse(): array {
		return [
			'status' => 'ERR',
			'messages' => [
				'membershipFee' => 'too-low'
			]
		];
	}

	public function testInvalidFee_failureResponseIsReturned(): void {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/validate-fee',
			[ 'membershipFee' => 'such', 'paymentIntervalInMonths' => '6', 'addressType' => 'person' ]
		);

		$this->assertSame(
			[
				'status' => 'ERR',
				'messages' => [
					'membershipFee' => 'not-money'
				]
			],
			json_decode( $client->getResponse()->getContent(), true )
		);
	}

}
