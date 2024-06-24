<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use PHPUnit\Framework\Attributes\CoversClass;
use WMDE\Fundraising\Frontend\App\Controllers\Validation\ValidateMembershipPaymentController;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\PaymentContext\Domain\PaymentType;

#[CoversClass( ValidateMembershipPaymentController::class )]
class ValidateFeeRouteTest extends WebRouteTestCase {

	public function testGivenValidParameters_successResponseIsReturned(): void {
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
		$this->assertSame(
			[
				'status' => 'ERR',
				'messages' => [ 'fee' => 'error_too_low' ]
			],
			json_decode( $response->getContent() ?: '', true )
		);
	}

	public function testGivenEmptyParameters_failureResponseIsReturned(): void {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/validate-fee',
		);

		$response = $client->getResponse();
		$this->assertErrorJsonResponse( $response );
		$this->assertSame(
			[
				'status' => 'ERR',
				'messages' => [
					'applicant-type' => 'invalid-applicant-type',
					'fee' => 'cannot_parse_fee'
				]
			],
			json_decode( $response->getContent() ?: '', true )
		);
	}

	public function testGivenInvalidFeeValue_failureResponseIsReturned(): void {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/validate-fee',
			[
				'membershipFee' => 'NOTHING!',
				'paymentIntervalInMonths' => '6',
				'addressType' => 'firma',
				'paymentType' => PaymentType::DirectDebit->value
			]
		);

		$response = $client->getResponse();
		$this->assertErrorJsonResponse( $response );
		$this->assertSame(
			[
				'status' => 'ERR',
				'messages' => [ 'fee' => 'cannot_parse_fee' ]
			],
			json_decode( $response->getContent() ?: '', true )
		);
	}

}
