<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\PaymentContext\Domain\PaymentType;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Validation\ValidateFeeController
 */
class ValidateFeeRouteTest extends WebRouteTestCase {

	public function testGivenValidParameters_successResponseIsReturned(): void {
		$this->markTestIncomplete( "We need to add a use case in Memberships to encapsulate required functionality" );

		$client = $this->createClient();

		$client->request(
			'POST',
			'/validate-fee',
			[
				'membershipFee' => '1234',
				'paymentIntervalInMonths' => '6',
				'addressType' => 'person',
				'paymentType' => PaymentType::DirectDebit->value
			]
		);

		$this->assertJsonSuccessResponse(
			[ 'status' => 'OK' ],
			$client->getResponse()
		);
	}

	public function testGivenInvalidParameters_failureResponseIsReturned(): void {
		$this->markTestIncomplete( "We need to add a use case in Memberships to encapsulate required functionality" );

		$client = $this->createClient();

		$client->request(
			'POST',
			'/validate-fee',
			[
				'membershipFee' => '1234',
				'paymentIntervalInMonths' => '6',
				'addressType' => 'firma',
				'paymentType' => PaymentType::DirectDebit->value
			]
		);

		$response = $client->getResponse();
		$this->assertErrorJsonResponse( $response );
		$this->assertSame( $this->newErrorResponse(), json_decode( $response->getContent(), true ) );
	}

	private function newErrorResponse(): array {
		return [
			'status' => 'ERR',
			'messages' => [
				'membershipFee' => 'error_too_low'
			]
		];
	}

	public function testInvalidFee_failureResponseIsReturned(): void {
		$this->markTestIncomplete( "We need to add a use case in Memberships to encapsulate required functionality" );

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
