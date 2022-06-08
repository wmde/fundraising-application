<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Donation\NewDonationController
 */
class NewDonationRouteTest extends WebRouteTestCase {

	/** @dataProvider paymentInputProvider */
	public function testGivenPaymentInput_paymentDataIsInitiallyValidated( array $validPaymentInput, array $expected ): void {
		$this->markTestIncomplete( "This should work again when we finish updating the membership controllers" );

		$client = $this->createClient();
		$client->request(
			'POST',
			'/donation/new',
			$validPaymentInput
		);

		$this->assertStringContainsString(
			'Payment data: ' . $expected['validity'],
			$client->getResponse()->getContent()
		);

		$this->assertStringContainsString(
			'Amount: ' . $expected['amount'] . "\n",
			$client->getResponse()->getContent()
		);

		$this->assertStringContainsString(
			'isCustomAmount: ' . ( $expected['isCustomAmount'] ? '1' : '' ) . "\n",
			$client->getResponse()->getContent()
		);
	}

	public function paymentInputProvider(): array {
		return [
			[
				[
					'amount' => '10000',
					'paymentType' => 'BEZ',
					'interval' => '0'
				],
				[
					'validity' => 'valid',
					'amount' => '10000',
					'isCustomAmount' => false
				]
			],
			[
				[
					'amount' => '12345',
					'paymentType' => 'PPL',
					'interval' => 6
				],
				[
					'validity' => 'valid',
					'amount' => '12345',
					'isCustomAmount' => true
				]
			],
			[
				[
					'amount' => '870',
					'paymentType' => 'BEZ',
					'interval' => '0'
				],
				[
					'validity' => 'valid',
					'amount' => '870',
					'isCustomAmount' => true
				]
			],
			[
				[
					'amount' => '0',
					'paymentType' => 'PPL',
					'interval' => 6
				],
				[
					'validity' => 'invalid',
					'amount' => '0',
					'isCustomAmount' => false
				]
			],
			[
				[
					'amount' => '10000',
					'paymentType' => 'BTC',
					'interval' => 6
				],
				[
					'validity' => 'invalid',
					'amount' => '10000',
					'isCustomAmount' => false
				]
			]
		];
	}

	public function testWhenPassingTrackingData_itGetsPassedToThePresenter(): void {
		$this->markTestIncomplete( "This should work again when we finish updating the membership controllers" );

		$client = $this->createClient();
		$client->request(
			'POST',
			'/donation/new',
			[
				'impCount' => 12,
				'bImpCount' => 3
			]
		);

		$response = $client->getResponse()->getContent();
		$this->assertStringContainsString( 'Impression Count: 12', $response );
		$this->assertStringContainsString( 'Banner Impression Count: 3', $response );
	}
}
