<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Silex\Application;
use Symfony\Component\HttpKernel\Client;

use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
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

		$this->assertContains(
			'isCustomAmount: ' . ( $expected['isCustomAmount'] ? '1' : '' ) . "\n",
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
					'formattedAmount' => '100,00',
					'isCustomAmount' => false
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
					'formattedAmount' => '123,45',
					'isCustomAmount' => true
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
					'formattedAmount' => '8,70',
					'isCustomAmount' => true
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
					'formattedAmount' => '0,00',
					'isCustomAmount' => false
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

	// The following tests use the default route (which gets redirected to donation/new) to establish

	public function testWhenTrackableInputDataIsSubmitted_theyAreStoredInSession(): void {
		$this->createAppEnvironment( [], function ( Client $client, FunFunFactory $factory, Application $app ): void {
			$client->request(
				'GET',
				'/',
				[
					'betrag' => '5,00',
					'periode' => 3,
					'zahlweise' => 'BEZ'
				]
			);

			$piwikTracking = $app['session']->get( 'piwikTracking' );
			$this->assertSame( 'BEZ', $piwikTracking['paymentType'] );
			$this->assertSame( 3, $piwikTracking['paymentInterval'] );
			$this->assertSame( '5,00', $piwikTracking['paymentAmount'] );
		} );
	}

	public function testWhenTolstojNovelIsPassed_isIsNotStoredInSession(): void {
		$this->createAppEnvironment( [], function ( Client $client, FunFunFactory $factory, Application $app ): void {

			$client->request(
				'GET',
				'/',
				[
					'betrag' => '5,00',
					'periode' => 3,
					'zahlweise' => 'Eh bien, mon prince. Gênes et Lucques ne sont plus que des apanages, des поместья, de la ' .
						'famille Buonaparte. Non, je vous préviens que si vous ne me dites pas que nous avons la guerre, si ' .
						'vous vous permettez encore de pallier toutes les infamies, toutes les atrocités de cet Antichrist ' .
						'(ma parole, j’y crois) — je ne vous connais plus, vous n’êtes plus mon ami, vous n’êtes plus мой ' .
						'верный раб, comme vous dites. Ну, здравствуйте,' .
						'здравствуйте. Je vois que je vous fais peur, ' .
						'садитесь и рассказывайте.'
				]
			);

			$piwikTracking = $app['session']->get( 'piwikTracking' );
			$this->assertArrayNotHasKey( 'paymentType', $piwikTracking );
			$this->assertSame( 3, $piwikTracking['paymentInterval'] );
			$this->assertSame( '5,00', $piwikTracking['paymentAmount'] );
		} );
	}

	public function testWhenParameterIsOmitted_itIsNotStoredInSession(): void {
		$this->createAppEnvironment( [], function ( Client $client, FunFunFactory $factory, Application $app ): void {

			$client->request(
				'GET',
				'/',
				[
					'betrag' => '5,00',
					'zahlweise' => 'BEZ'
				]
			);

			$piwikTracking = $app['session']->get( 'piwikTracking' );
			$this->assertSame( 'BEZ', $piwikTracking['paymentType'] );
			$this->assertSame( '5,00', $piwikTracking['paymentAmount'] );
			$this->assertArrayNotHasKey( 'paymentInterval', $piwikTracking );
		} );
	}

	public function testAllPaymentTypesAreOffered(): void {
		$client = $this->createClient( [ 'skin' => [ 'default' => '10h16' ] ] );
		$client->request(
			'GET',
			'/'
		);
		$crawler = $client->getCrawler();

		$this->assertSame( 1, $crawler->filter( '#donation-payment input[name="zahlweise"][value="BEZ"]' )->count() );
		$this->assertSame( 1, $crawler->filter( '#donation-payment input[name="zahlweise"][value="UEB"]' )->count() );
		$this->assertSame( 1, $crawler->filter( '#donation-payment input[name="zahlweise"][value="MCP"]' )->count() );
		$this->assertSame( 1, $crawler->filter( '#donation-payment input[name="zahlweise"][value="PPL"]' )->count() );
		$this->assertSame( 1, $crawler->filter( '#donation-payment input[name="zahlweise"][value="SUB"]' )->count() );
	}

	public function testSofortPaymentTypeCanByDisabledViaQuery(): void {
		$client = $this->createClient( [ 'skin' => [ 'default' => '10h16' ] ] );
		$client->request(
			'GET',
			'/',
			[ 'pmt' => '0' ]
		);
		$crawler = $client->getCrawler();

		$this->assertSame( 0, $crawler->filter( '#donation-payment input[name="zahlweise"][value="SUB"]' )->count() );
	}


}
