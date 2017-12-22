<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class NewDonationRouteTest extends WebRouteTestCase {

	/** @dataProvider paymentInputProvider */
	public function testGivenPaymentInput_paymentDataIsInitiallyValidated( array $validPaymentInput, array $expected ): void {
		$client = $this->createClient();
		$client->request(
			'POST',
			'/donation/new',
			$validPaymentInput
		);

		$this->assertContains(
			'Payment data: ' . $expected['validity'],
			$client->getResponse()->getContent()
		);

		$this->assertContains(
			'Amount: ' . $expected['formattedAmount'] . "\n",
			$client->getResponse()->getContent()
		);
	}

	public function paymentInputProvider(): array {
		return [
			[
				[
					'betrag_auswahl' => '100',
					'zahlweise' => 'BEZ',
					'periode' => '0'
				],
				[
					'validity' => 'valid',
					'formattedAmount' => '100,00'
				]
			],
			[
				[
					'amountGiven' => '123.45',
					'zahlweise' => 'PPL',
					'periode' => 6
				],
				[
					'validity' => 'valid',
					'formattedAmount' => '123,45'
				]
			],
			[
				[
					'amountGiven' => '8.70',
					'zahlweise' => 'BEZ',
					'periode' => '0'
				],
				[
					'validity' => 'valid',
					'formattedAmount' => '8,70'
				]
			],
			[
				[
					'betrag_auswahl' => '0',
					'zahlweise' => 'PPL',
					'periode' => 6
				],
				[
					'validity' => 'invalid',
					'formattedAmount' => '0,00'
				]
			],
			[
				[
					'betrag_auswahl' => '100',
					'zahlweise' => 'BTC',
					'periode' => 6
				],
				[
					'validity' => 'invalid',
					'formattedAmount' => '100,00'
				]
			]
		];
	}

	public function testWhenPassingTrackingData_itGetsPassedToThePresenter(): void {
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
		$this->assertContains( 'Impression Count: 12', $response );
		$this->assertContains( 'Banner Impression Count: 3', $response );
	}

}
