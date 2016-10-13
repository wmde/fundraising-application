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
	public function testGivenPaymentInput_paymentDataIsInitiallyValidated( $validPaymentInput, $expectedValidity ) {
		$client = $this->createClient();
		$client->request(
			'POST',
			'/donation/new',
			$validPaymentInput
		);

		$this->assertContains(
			'Payment data: ' . $expectedValidity,
			$client->getResponse()->getContent()
		);
	}

	public function paymentInputProvider() {
		return [
			[
				[
					'betrag_auswahl' => '100',
					'zahlweise' => 'BEZ',
					'periode' => '0'
				],
				'valid'
			],
			[
				[
					'amountGiven' => '123.45',
					'zahlweise' => 'PPL',
					'periode' => 6
				],
				'valid'
			],
			[
				[
					'betrag_auswahl' => '0',
					'zahlweise' => 'PPL',
					'periode' => 6
				],
				'invalid'
			],
			[
				[
					'betrag_auswahl' => '100',
					'zahlweise' => 'BTC',
					'periode' => 6
				],
				'invalid'
			]
		];
	}

}
