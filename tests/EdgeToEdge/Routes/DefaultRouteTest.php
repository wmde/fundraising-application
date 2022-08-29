<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Donation\NewDonationController
 */
class DefaultRouteTest extends WebTestCase {

	public function testWhenFormParametersArePassedInRequest_theyArePassedToTheTemplate(): void {
		$this->markTestIncomplete( "This should work again when we finish updating the donation controllers" );

		$client = static::createClient();
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
		$this->assertStringContainsString( 'Amount: 1234', $responseContent );
		$this->assertStringContainsString( 'Payment type: UEB', $responseContent );
		$this->assertStringContainsString( 'Interval: 6', $responseContent );
	}

	public function testWhenFormParametersContainNegativeAmount_zeroAmountIsPassedToTheTemplate(): void {
		$this->markTestIncomplete( "This should work again when we finish updating the donation controllers" );

		$client = static::createClient();
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
		$this->assertStringContainsString( 'Amount: 0', $responseContent );
		$this->assertStringContainsString( 'Payment type: UEB', $responseContent );
		$this->assertStringContainsString( 'Interval: 6', $responseContent );
	}
}
