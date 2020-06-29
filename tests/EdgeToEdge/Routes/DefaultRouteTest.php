<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @license GPL-2.0-or-later
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DefaultRouteTest extends WebRouteTestCase {

	public function testWhenFormParametersArePassedInRequest_theyArePassedToTheTemplate(): void {
		$client = $this->createClient();
		$client->request(
			'GET',
			'/',
			[
				'amount' => '1234',
				'paymentType' => 'UEB',
				'interval' => 6
			]
		);

		$responseContent = $client->getResponse()->getContent();
		$this->assertStringContainsString( 'Amount: 12,34', $responseContent );
		$this->assertStringContainsString( 'Payment type: UEB', $responseContent );
		$this->assertStringContainsString( 'Interval: 6', $responseContent );
	}

	public function testWhenFormParametersContainNegativeAmount_zeroAmountIsPassedToTheTemplate(): void {
		$client = $this->createClient();
		$client->request(
			'GET',
			'/',
			[
				'amount' => '-1234',
				'paymentType' => 'UEB',
				'interval' => 6
			]
		);

		$responseContent = $client->getResponse()->getContent();
		$this->assertStringContainsString( 'Amount: 0,00', $responseContent );
		$this->assertStringContainsString( 'Payment type: UEB', $responseContent );
		$this->assertStringContainsString( 'Interval: 6', $responseContent );
	}
}
