<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DefaultRouteTest extends WebRouteTestCase {

	public function testWhenFormParametersArePassedInRequest_theyArePassedToTheTemplate(): void {
		$client = $this->createClient();
		$client->request(
			'GET',
			'/',
			[
				'amountGiven' => '12.34',
				'zahlweise' => 'UEB',
				'periode' => 6
			]
		);

		$responseContent = $client->getResponse()->getContent();
		$this->assertContains( 'Amount: 12,34', $responseContent );
		$this->assertContains( 'Payment type: UEB', $responseContent );
		$this->assertContains( 'Interval: 6', $responseContent );
	}

	public function testWhenFormParametersContainNegativeAmount_zeroAmountIsPassedToTheTemplate(): void {
		$client = $this->createClient();
		$client->request(
			'GET',
			'/',
			[
				'amountGiven' => '-12.34',
				'zahlweise' => 'UEB',
				'periode' => 6
			]
		);

		$responseContent = $client->getResponse()->getContent();
		$this->assertContains( 'Amount: 0,00', $responseContent );
		$this->assertContains( 'Payment type: UEB', $responseContent );
		$this->assertContains( 'Interval: 6', $responseContent );
	}
}
