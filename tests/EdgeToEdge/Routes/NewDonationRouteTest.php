<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Silex\Application;
use Symfony\Component\HttpKernel\Client;

use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\OverridingCampaignConfigurationLoader;

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

		$this->assertContains(
			'isCustomAmount: ' . ( $expected['isCustomAmount'] ? '1' : '' ) . "\n",
			$client->getResponse()->getContent()
		);
	}

	public function paymentInputProvider(): array {
		return [
			[
				[
					'amount' => '10000',
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
					'amount' => '12345',
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
					'amount' => '870',
					'zahlweise' => 'BEZ',
					'periode' => '0'
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
					'zahlweise' => 'PPL',
					'periode' => 6
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
					'zahlweise' => 'BTC',
					'periode' => 6
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
		$this->assertContains( 'Impression Count: 12', $response );
		$this->assertContains( 'Banner Impression Count: 3', $response );
	}

	public function testAllPaymentTypesAreOffered(): void {
		$client = $this->createClient(
			[],
			function ( FunFunFactory $factory ): void {
				$this->setDefaultSkin( $factory, '10h16' );
			}
		);
		$client->request(
			'GET',
			'/donation/new'
		);
		$crawler = $client->getCrawler();

		$this->assertSame( 1, $crawler->filter( '#donation-payment input[name="zahlweise"][value="BEZ"]' )->count() );
		$this->assertSame( 1, $crawler->filter( '#donation-payment input[name="zahlweise"][value="UEB"]' )->count() );
		$this->assertSame( 1, $crawler->filter( '#donation-payment input[name="zahlweise"][value="MCP"]' )->count() );
		$this->assertSame( 1, $crawler->filter( '#donation-payment input[name="zahlweise"][value="PPL"]' )->count() );
		$this->assertSame( 1, $crawler->filter( '#donation-payment input[name="zahlweise"][value="SUB"]' )->count() );
	}

	private function setDefaultSkin( FunFunFactory $factory, string $skinName ): void {
		$factory->setCampaignConfigurationLoader(
			new OverridingCampaignConfigurationLoader(
				$factory->getCampaignConfigurationLoader(),
				[ 'skins' => [ 'default_bucket' => $skinName ] ]
			)
		);
	}
}
