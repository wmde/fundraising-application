<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Silex\Application;
use Symfony\Component\HttpKernel\Client;

use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\OverridingCampaignConfigurationLoader;

/**
 * @license GPL-2.0-or-later
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
					'formattedAmount' => '100,00',
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
					'formattedAmount' => '123,45',
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
					'formattedAmount' => '8,70',
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
					'formattedAmount' => '0,00',
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
					'formattedAmount' => '100,00',
					'isCustomAmount' => false
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
		$this->assertStringContainsString( 'Impression Count: 12', $response );
		$this->assertStringContainsString( 'Banner Impression Count: 3', $response );
	}
}
