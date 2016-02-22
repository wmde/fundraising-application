<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use WMDE\Fundraising\Frontend\Tests\System\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;

/**
 * @licence GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ValidateAmountRouteTest extends WebRouteTestCase {

	public function testGivenValidAmount_successResponseIsReturned() {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/validate-amount',
			[ 'amount' => '23', 'paymentType' => PaymentType::BANK_TRANSFER ]
		);

		$this->assertJsonSuccessResponse(
			[ 'status' => 'OK' ],
			$client->getResponse()
		);
	}

	public function testGivenInvalidAmount_failureResponseIsReturned() {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/validate-amount',
			[ 'amount' => '-1', 'paymentType' => PaymentType::BANK_TRANSFER ]
		);

		$this->assertErrorJsonResponse( $client->getResponse() );
	}

}
