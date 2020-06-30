<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Sunset\Routes;

use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\AddDonationController
 */
class NewDonationLegacyParametersTest extends WebRouteTestCase {

	public function testFeatureIsDeprecated(): void {
		$expirationDate = new \DateTime( '2021-01-31' );
		$now = new \DateTime();

		$this->assertTrue(
			$expirationDate > $now,
			'Legacy parameters are no longer supported. Please replace the "FallbackRequestValueReader" class with ' .
			' scalar default values and delete the class and this test.' );
	}

	/** @dataProvider legacyPaymentInputProvider */
	public function testGivenLegacyPaymentInput_paymentDataIsInitiallyValidated( array $validPaymentInput, array $expected ): void {
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
			'Amount: ' . $expected['formattedAmount'] . "\n",
			$client->getResponse()->getContent()
		);

		$this->assertStringContainsString(
			'isCustomAmount: ' . ( $expected['isCustomAmount'] ? '1' : '' ) . "\n",
			$client->getResponse()->getContent()
		);
	}

	public function legacyPaymentInputProvider(): array {
		return [
			[
				[
					'betrag' => '100,00',
					'zahlweise' => 'BEZ',
					'periode' => '0'
				],
				[
					'validity' => 'valid',
					'formattedAmount' => '100,00',
					'isCustomAmount' => false
				]
			],
			[
				[
					'betrag_auswahl' => '123.45',
					'zahlweise' => 'PPL',
					'periode' => 6
				],
				[
					'validity' => 'valid',
					'formattedAmount' => '123,45',
					'isCustomAmount' => true
				]
			],
			[
				[
					'amountGiven' => '8,70',
					'paymentType' => 'BEZ',
					'interval' => '0'
				],
				[
					'validity' => 'valid',
					'formattedAmount' => '8,70',
					'isCustomAmount' => true
				]
			],
		];
	}
}
