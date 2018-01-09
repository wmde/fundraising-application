<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PaymentMethods;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ValidatePaymentDataRouteTest extends WebRouteTestCase {

	public function testGivenValidAmount_successResponseIsReturned(): void {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/validate-payment-data',
			[ 'amount' => '23', 'paymentType' => PaymentMethods::BANK_TRANSFER ]
		);

		$this->assertJsonSuccessResponse(
			[ 'status' => 'OK' ],
			$client->getResponse()
		);
	}

	public function testGivenInvalidAmount_failureResponseIsReturned(): void {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/validate-payment-data',
			[ 'amount' => '-1', 'paymentType' => PaymentMethods::BANK_TRANSFER ]
		);

		$this->assertErrorJsonResponse( $client->getResponse() );
	}

}
