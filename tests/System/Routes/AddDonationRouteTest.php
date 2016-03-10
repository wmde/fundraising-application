<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use Swift_NullTransport;
use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\System\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Infrastructure\Messenger;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class AddDonationRouteTest extends WebRouteTestCase {

	public function setUp() {
		if ( !function_exists( 'lut_init' ) ) {
			$this->markTestSkipped( 'The konto_check needs to be installed!' );
		}
		parent::setUp();
	}

	public function testGivenValidRequest_donationGetsPersisted() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setMessenger( new Messenger(
				Swift_NullTransport::newInstance(),
				$factory->getOperatorAddress()
			) );

			$client->setServerParameter( 'HTTP_REFERER', 'https://en.wikipedia.org/wiki/Karla_Kennichnich' );
			$client->followRedirects( false );

			$client->request(
				'POST',
				'/donation/add',
				$this->newValidFormInput()
			);

			$response = $client->getResponse();

			$donation = $this->getDonationFromDatabase( $factory );

			$data = unserialize( base64_decode( $donation->getData() ) );
			$this->assertSame( 5.51, $donation->getAmount() );
			$this->assertSame( 'BEZ', $donation->getPaymentType() );
			$this->assertSame( 0, $donation->getPeriod() );
			$this->assertSame( 'DE12500105170648489890', $data['iban'] );
			$this->assertSame( 'INGDDEFFXXX', $data['bic'] );
			$this->assertSame( '0648489890', $data['konto'] );
			$this->assertSame( '50010517', $data['blz'] );
			$this->assertSame( 'ING-DiBa', $data['bankname'] );
			$this->assertSame( 'person', $data['adresstyp'] );
			$this->assertSame( 'Frau', $data['anrede'] );
			$this->assertSame( 'Prof. Dr.', $data['titel'] );
			$this->assertSame( '', $data['firma'] );
			$this->assertSame( 'Karla', $data['vorname'] );
			$this->assertSame( 'Kennichnich', $data['nachname'] );
			$this->assertSame( 'Prof. Dr. Karla Kennichnich', $donation->getName() );
			$this->assertSame( 'Lehmgasse 12', $data['strasse'] );
			$this->assertSame( '12345', $data['plz'] );
			$this->assertSame( 'Einort', $data['ort'] );
			$this->assertSame( 'Einort', $donation->getCity() );
			$this->assertSame( 'DE', $data['country'] );
			$this->assertSame( 'karla@kennichnich.de', $data['email'] );
			$this->assertSame( 'karla@kennichnich.de', $donation->getEmail() );
			$this->assertSame( 'test/gelb', $data['tracking'] );
			$this->assertSame( 3, $data['impCount'] );
			$this->assertSame( 1, $data['bImpCount'] );
			$this->assertSame( 'Default', $data['layout'] );
			$this->assertSame( 'blue', $data['color'] );
			$this->assertSame( 'default', $data['skin'] );
			$this->assertSame( 'en.wikipedia.org', $data['source'] );
			$this->assertSame( 'N', $donation->getStatus() );
			$this->assertSame( true, $donation->getInfo() );

			// TODO: assert tokens are set
			// $this->assertRegExp( '/[0-9a-f]{32}/', $data['token'] );
			// $this->assertRegExp( '/[0-9a-f]{32}/', $data['utoken'] );
			// $this->assertGreaterThan( ( new \DateTime() )->format( 'Y-m-d H:i:s' ), $data['utoken_expiry'] );

			$this->assertContains( '5,51', $response->getContent() );
			$this->assertContains( 'einmalig', $response->getContent() );
		} );
	}

	private function newValidFormInput() {
		return [
			'betrag' => '5,51',
			'zahlweise' => 'BEZ',
			'periode' => 0,
			'iban' => 'DE12500105170648489890',
			'bic' => 'INGDDEFFXXX',
			'konto' => '0648489890',
			'blz' => '50010517',
			'bankname' => 'ING-DiBa',
			'adresstyp' => 'person',
			'anrede' => 'Frau',
			'titel' => 'Prof. Dr.',
			'firma' => '',
			'vorname' => 'Karla',
			'nachname' => 'Kennichnich',
			'strasse' => 'Lehmgasse 12',
			'plz' => '12345',
			'ort' => 'Einort',
			'country' => 'DE',
			'email' => 'karla@kennichnich.de',
			'info' => '1',
			'piwik_campaign' => 'test',
			'piwik_kwd' => 'gelb',
			'impCount' => '3',
			'bImpCount' => '1',
			'layout' => 'Default',
			'color' => 'blue',
			'skin' => 'default',
		];
	}

	public function testGivenValidBankTransferRequest_donationGetsPersisted() {
		/**
		 * @var FunFunFactory
		 */
		$factory = null;

		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setMessenger( new Messenger(
				Swift_NullTransport::newInstance(),
				$factory->getOperatorAddress()
			) );

			$client->setServerParameter( 'HTTP_REFERER', 'https://en.wikipedia.org/wiki/Karla_Kennichnich' );
			$client->followRedirects( false );

			$client->request(
				'POST',
				'/donation/add',
				$this->newValidBankTransferInput()
			);

			$donation = $this->getDonationFromDatabase( $factory );

			$data = unserialize( base64_decode( $donation->getData() ) );
			$this->assertSame( 12.34, $donation->getAmount() );
			$this->assertSame( 'UEB', $donation->getPaymentType() );
			$this->assertSame( 0, $donation->getPeriod() );
			$this->assertSame( 'person', $data['adresstyp'] );
			$this->assertSame( 'Frau', $data['anrede'] );
			$this->assertSame( 'Prof. Dr.', $data['titel'] );
			$this->assertSame( '', $data['firma'] );
			$this->assertSame( 'Karla', $data['vorname'] );
			$this->assertSame( 'Kennichnich', $data['nachname'] );
			$this->assertSame( 'Prof. Dr. Karla Kennichnich', $donation->getName() );
			$this->assertSame( 'Lehmgasse 12', $data['strasse'] );
			$this->assertSame( '12345', $data['plz'] );
			$this->assertSame( 'Einort', $data['ort'] );
			$this->assertSame( 'Einort', $donation->getCity() );
			$this->assertSame( 'DE', $data['country'] );
			$this->assertSame( 'karla@kennichnich.de', $data['email'] );
			$this->assertSame( 'karla@kennichnich.de', $donation->getEmail() );
			$this->assertSame( 'test/gelb', $data['tracking'] );
			$this->assertSame( 3, $data['impCount'] );
			$this->assertSame( 1, $data['bImpCount'] );
			$this->assertSame( 'Default', $data['layout'] );
			$this->assertSame( 'blue', $data['color'] );
			$this->assertSame( 'default', $data['skin'] );
			$this->assertSame( 'en.wikipedia.org', $data['source'] );
			$this->assertSame( true, $donation->getInfo() );

			$this->assertSame( 'Z', $donation->getStatus() );
			$this->assertRegExp( '/W-Q-[A-Z]{6}-[A-Z]/', $donation->getTransferCode() );
		} );
	}

	private function newValidBankTransferInput() {
		return [
			'betrag' => '12,34',
			'zahlweise' => 'UEB',
			'periode' => 0,
			'adresstyp' => 'person',
			'anrede' => 'Frau',
			'titel' => 'Prof. Dr.',
			'firma' => '',
			'vorname' => 'Karla',
			'nachname' => 'Kennichnich',
			'strasse' => 'Lehmgasse 12',
			'plz' => '12345',
			'ort' => 'Einort',
			'country' => 'DE',
			'email' => 'karla@kennichnich.de',
			'info' => '1',
			'piwik_campaign' => 'test',
			'piwik_kwd' => 'gelb',
			'impCount' => '3',
			'bImpCount' => '1',
			'layout' => 'Default',
			'color' => 'blue',
			'skin' => 'default',
		];
	}

	public function testGivenComplementableBankData_donationStillGetsPersisted() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setMessenger( new Messenger(
				Swift_NullTransport::newInstance(),
				$factory->getOperatorAddress()
			) );

			$client->setServerParameter( 'HTTP_REFERER', 'https://en.wikipedia.org/wiki/Karla_Kennichnich' );
			$client->followRedirects( false );

			$client->request(
				'POST',
				'/donation/add',
				$this->newComplementableFormInput()
			);

			$donation = $this->getDonationFromDatabase( $factory );

			$data = unserialize( base64_decode( $donation->getData() ) );
			$this->assertSame( 'DE12500105170648489890', $data['iban'] );
			$this->assertSame( 'INGDDEFFXXX', $data['bic'] );
			$this->assertSame( '0648489890', $data['konto'] );
			$this->assertSame( '50010517', $data['blz'] );
			$this->assertSame( 'ING-DiBa', $data['bankname'] );
		} );
	}

	private function newComplementableFormInput() {
		return [
			'betrag' => '5,51',
			'zahlweise' => 'BEZ',
			'periode' => 0,
			'iban' => 'DE12500105170648489890',
			'adresstyp' => 'person',
			'anrede' => 'Frau',
			'titel' => 'Prof. Dr.',
			'vorname' => 'Karla',
			'nachname' => 'Kennichnich',
			'strasse' => 'Lehmgasse 12',
			'plz' => '12345',
			'ort' => 'Einort',
			'country' => 'DE',
			'email' => 'karla@kennichnich.de',
		];
	}

	private function getDonationFromDatabase( FunFunFactory $factory ): Donation {
		$donationRepo = $factory->getEntityManager()->getRepository( Donation::class );
		$donation = $donationRepo->find( 1 );
		$this->assertInstanceOf( Donation::class, $donation );
		return $donation;
	}

	public function testGivenValidPayPalData_redirectsToPayPal() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setMessenger( new Messenger(
				Swift_NullTransport::newInstance(),
				$factory->getOperatorAddress()
			) );
			$client->followRedirects( false );

			$client->request(
				'POST',
				'/donation/add',
				$this->newValidPayPalInput()
			);

			$response = $client->getResponse();
			$this->assertSame( 302, $response->getStatusCode() );
			$this->assertContains( 'that.paymentprovider.com', $response->getContent() );
		} );
	}

	private function newValidPayPalInput() {
		return [
			'betrag' => '12,34',
			'zahlweise' => 'PPL',
			'periode' => 3,
			'adresstyp' => 'anonym',
		];
	}

	public function testGivenValidCreditCardData_showsIframeEmbeddingPage() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setMessenger( new Messenger(
				Swift_NullTransport::newInstance(),
				$factory->getOperatorAddress()
			) );

			$client->request(
				'POST',
				'/donation/add',
				$this->newValidCreditCardInput()
			);

			$response = $client->getResponse();
			$this->assertSame( 200, $response->getStatusCode() );

			$this->assertContains( 'thatother.paymentprovider.com', $response->getContent() );
		} );
	}

	private function newValidCreditCardInput() {
		return [
			'betrag' => '12,34',
			'zahlweise' => 'MCP',
			'periode' => 3,
			'adresstyp' => 'anonym',
		];
	}

}
