<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Silex\Application;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class AddDonationRouteTest extends WebRouteTestCase {

	const SOME_TOKEN = 'SomeToken';

	public function setUp() {
		if ( !function_exists( 'lut_init' ) ) {
			$this->markTestSkipped( 'The konto_check needs to be installed!' );
		}
		parent::setUp();
	}

	public function testGivenValidRequest_donationGetsPersisted() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();

			$client->setServerParameter( 'HTTP_REFERER', 'https://en.wikipedia.org/wiki/Karla_Kennichnich' );
			$client->followRedirects( false );

			$client->request(
				'POST',
				'/donation/add',
				$this->newValidFormInput()
			);

			$this->assertIsExpectedDonation( $this->getDonationFromDatabase( $factory ) );
		} );
	}

	public function testWhenDonationGetsPersisted_timestampIsStoredInCookie() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();

			$client->request(
				'POST',
				'/donation/add',
				$this->newValidFormInput()
			);

			$cookie = $client->getCookieJar()->get( 'donation_timestamp' );
			$this->assertNotNull( $cookie );
			$donationTimestamp = new \DateTime( $cookie->getValue() );
			$this->assertEquals( time(), $donationTimestamp->getTimestamp(), 'Timestamp should be not more than 5 seconds old', 5.0 );

		} );
	}

	public function testWhenMultipleDonationFormSubmissions_requestGetsRejected() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();
			$client->getCookieJar()->set( new Cookie( 'donation_timestamp', $this->getPastTimestamp() ) );

			$client->request(
				'POST',
				'/donation/add',
				$this->newValidFormInput()
			);

			$this->assertContains( 'Sie haben vor sehr kurzer Zeit bereits gespendet', $client->getResponse()->getContent() );
		} );
	}

	public function testWhenMultipleDonationsInAccordanceToTimeLimit_requestIsNotRejected() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();
			$client->getCookieJar()->set(
				new Cookie(
					'donation_timestamp',
					$this->getPastTimestamp( 'PT35M' )
				)
			);

			$client->request(
				'POST',
				'/donation/add',
				$this->newValidFormInput()
			);

			$this->assertNotContains( 'Sie haben vor sehr kurzer Zeit bereits gespendet', $client->getResponse()->getContent() );
		} );
	}

	private function getPastTimestamp( string $interval = 'PT10S' ) {
		return ( new \DateTime() )->sub( new \DateInterval( $interval ) )->format( 'Y-m-d H:i:s' );
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
			'addressType' => 'person',
			'salutation' => 'Frau',
			'title' => 'Prof. Dr.',
			'company' => '',
			'firstName' => 'Karla',
			'lastName' => 'Kennichnich',
			'street' => 'Lehmgasse 12',
			'postcode' => '12345',
			'city' => 'Einort',
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

	private function assertIsExpectedDonation( Donation $donation ) {
		$data = $donation->getDecodedData();
		$this->assertSame( '5.51', $donation->getAmount() );
		$this->assertSame( 'BEZ', $donation->getPaymentType() );
		$this->assertSame( 0, $donation->getPaymentIntervalInMonths() );
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
		$this->assertSame( 'Prof. Dr. Karla Kennichnich', $donation->getDonorFullName() );
		$this->assertSame( 'Lehmgasse 12', $data['strasse'] );
		$this->assertSame( '12345', $data['plz'] );
		$this->assertSame( 'Einort', $data['ort'] );
		$this->assertSame( 'Einort', $donation->getDonorCity() );
		$this->assertSame( 'DE', $data['country'] );
		$this->assertSame( 'karla@kennichnich.de', $data['email'] );
		$this->assertSame( 'karla@kennichnich.de', $donation->getDonorEmail() );
		$this->assertSame( 'test/gelb', $data['tracking'] );
		$this->assertSame( 3, $data['impCount'] );
		$this->assertSame( 1, $data['bImpCount'] );
		$this->assertSame( 'Default', $data['layout'] );
		$this->assertSame( 'blue', $data['color'] );
		$this->assertSame( 'default', $data['skin'] );
		$this->assertSame( 'en.wikipedia.org', $data['source'] );
		$this->assertSame( 'N', $donation->getStatus() );
		$this->assertTrue( $donation->getDonorOptsIntoNewsletter() );
	}

	public function testGivenValidRequest_confirmationPageContainsEnteredData() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();

			$client->request(
				'POST',
				'/donation/add',
				$this->newValidFormInput()
			);

			$response = $client->getResponse()->getContent();

			$this->assertContains( '<strong>5,51 €</strong>', $response );
			$this->assertContains( 'per Lastschrift', $response );
			$this->assertContains( 'einmalig', $response );
			$this->assertContains( 'DE12500105170648489890', $response );
			$this->assertContains( 'INGDDEFFXXX', $response );
			$this->assertContains( 'ING-DiBa', $response );
			$this->assertContains( 'Prof. Dr. Karla Kennichnich', $response );
			$this->assertContains( 'Lehmgasse 12', $response );
			$this->assertContains( '<span id="confirm-postcode">12345</span> <span id="confirm-city">Einort</span>', $response );
			$this->assertContains( 'karla@kennichnich.de', $response );
			$this->assertContains( '<div id="send-info"', $response );
		} );
	}

	public function testGivenValidBankTransferRequest_donationGetsPersisted() {
		/**
		 * @var FunFunFactory
		 */
		$factory = null;

		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();

			$client->setServerParameter( 'HTTP_REFERER', 'https://en.wikipedia.org/wiki/Karla_Kennichnich' );
			$client->followRedirects( false );

			$client->request(
				'POST',
				'/donation/add',
				$this->newValidBankTransferInput()
			);

			$donation = $this->getDonationFromDatabase( $factory );

			$data = $donation->getDecodedData();
			$this->assertSame( '12.34', $donation->getAmount() );
			$this->assertSame( 'UEB', $donation->getPaymentType() );
			$this->assertSame( 0, $donation->getPaymentIntervalInMonths() );
			$this->assertSame( 'person', $data['adresstyp'] );
			$this->assertSame( 'Frau', $data['anrede'] );
			$this->assertSame( 'Prof. Dr.', $data['titel'] );
			$this->assertSame( '', $data['firma'] );
			$this->assertSame( 'Karla', $data['vorname'] );
			$this->assertSame( 'Kennichnich', $data['nachname'] );
			$this->assertSame( 'Prof. Dr. Karla Kennichnich', $donation->getDonorFullName() );
			$this->assertSame( 'Lehmgasse 12', $data['strasse'] );
			$this->assertSame( '12345', $data['plz'] );
			$this->assertSame( 'Einort', $data['ort'] );
			$this->assertSame( 'Einort', $donation->getDonorCity() );
			$this->assertSame( 'DE', $data['country'] );
			$this->assertSame( 'karla@kennichnich.de', $data['email'] );
			$this->assertSame( 'karla@kennichnich.de', $donation->getDonorEmail() );
			$this->assertSame( 'test/gelb', $data['tracking'] );
			$this->assertSame( 3, $data['impCount'] );
			$this->assertSame( 1, $data['bImpCount'] );
			$this->assertSame( 'Default', $data['layout'] );
			$this->assertSame( 'blue', $data['color'] );
			$this->assertSame( 'default', $data['skin'] );
			$this->assertSame( 'en.wikipedia.org', $data['source'] );
			$this->assertSame( true, $donation->getDonorOptsIntoNewsletter() );

			$this->assertSame( 'Z', $donation->getStatus() );
			$this->assertRegExp( '/W-Q-[A-Z]{6}-[A-Z]/', $donation->getBankTransferCode() );
		} );
	}

	private function newValidBankTransferInput() {
		return [
			'betrag' => '12,34',
			'zahlweise' => 'UEB',
			'periode' => 0,
			'addressType' => 'person',
			'salutation' => 'Frau',
			'title' => 'Prof. Dr.',
			'company' => '',
			'firstName' => 'Karla',
			'lastName' => 'Kennichnich',
			'street' => 'Lehmgasse 12',
			'postcode' => '12345',
			'city' => 'Einort',
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
			$factory->setNullMessenger();

			$client->setServerParameter( 'HTTP_REFERER', 'https://en.wikipedia.org/wiki/Karla_Kennichnich' );
			$client->followRedirects( false );

			$client->request(
				'POST',
				'/donation/add',
				$this->newComplementableFormInput()
			);

			$donation = $this->getDonationFromDatabase( $factory );

			$data = $donation->getDecodedData();
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
			'addressType' => 'person',
			'salutation' => 'Frau',
			'title' => 'Prof. Dr.',
			'firstName' => 'Karla',
			'lastName' => 'Kennichnich',
			'street' => 'Lehmgasse 12',
			'postcode' => '12345',
			'city' => 'Einort',
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
			$factory->setNullMessenger();
			$client->followRedirects( false );

			$client->request(
				'POST',
				'/donation/add',
				$this->newValidPayPalInput()
			);

			$response = $client->getResponse();
			$this->assertSame( 302, $response->getStatusCode() );
			$this->assertContains( 'sandbox.paypal.com', $response->getContent() );
		} );
	}

	private function newValidPayPalInput() {
		return [
			'betrag' => '12,34',
			'zahlweise' => 'PPL',
			'periode' => 3,
			'addressType' => 'anonym',
		];
	}

	public function testGivenValidCreditCardData_showsIframeEmbeddingPage() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();

			$client->request(
				'POST',
				'/donation/add',
				$this->newValidCreditCardInput()
			);

			$response = $client->getResponse();
			$this->assertSame( 200, $response->getStatusCode() );
			$this->assertContains( 'Ich spende vierteljährlich 12,34 € per Kreditkarte.', $response->getContent() );
			$this->assertContains( 'thatother.paymentprovider.com', $response->getContent() );
		} );
	}

	private function newValidCreditCardInput() {
		return [
			'betrag' => '12,34',
			'zahlweise' => 'MCP',
			'periode' => 3,
			'addressType' => 'anonym',
		];
	}

	public function testGivenInvalidRequest_formIsReloadedAndPrefilled() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();

			$client->request(
				'POST',
				'/donation/add',
				$this->newInvalidFormInput()
			);

			$response = $client->getResponse()->getContent();

			$this->assertContains( 'Amount: 0,00', $response );
			$this->assertContains( 'Payment type: BEZ', $response );
			$this->assertContains( 'Interval: 3', $response );
			$this->assertContains( 'IBAN: DE12500105170648489890', $response );
			$this->assertContains( 'BIC: INGDDEFFXXX', $response );
			$this->assertContains( 'Bank name: ING-DiBa', $response );
			$this->assertContains( 'Address type: person', $response );
			$this->assertContains( 'Salutation: Frau', $response );
			$this->assertContains( 'Title: Prof. Dr.', $response );
			$this->assertContains( 'Company: ', $response );
			$this->assertContains( 'First name: Karla', $response );
			$this->assertContains( 'Last name: Kennichnich', $response );
			$this->assertContains( 'Street: Lehmgasse 12', $response );
			$this->assertContains( 'Postal code: 12345', $response );
			$this->assertContains( 'City: Einort', $response );
			$this->assertContains( 'Country code: DE', $response );
			$this->assertContains( 'Email address: karla@kennichnich.de', $response );
		} );
	}

	private function newInvalidFormInput() {
		return [
			'betrag' => '0',
			'zahlweise' => 'BEZ',
			'periode' => 3,
			'iban' => 'DE12500105170648489890',
			'bic' => 'INGDDEFFXXX',
			'konto' => '0648489890',
			'blz' => '50010517',
			'bankname' => 'ING-DiBa',
			'addressType' => 'person',
			'salutation' => 'Frau',
			'title' => 'Prof. Dr.',
			'company' => '',
			'firstName' => 'Karla',
			'lastName' => 'Kennichnich',
			'street' => 'Lehmgasse 12',
			'postcode' => '12345',
			'city' => 'Einort',
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

	public function testGivenInvalidAnonymousRequest_formIsReloadedAndPrefilled() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();

			$client->request(
				'POST',
				'/donation/add',
				$this->newAnonymousFormInput()
			);

			$response = $client->getResponse()->getContent();

			$this->assertContains( 'Amount: 0', $response );
			$this->assertContains( 'Payment type: UEB', $response );
			$this->assertContains( 'Interval: 1', $response );
			$this->assertContains( 'Value of field "amount" violates rule: Amount too low', $response );
		} );
	}

	private function newAnonymousFormInput() {
		return [
			'betrag' => '0',
			'zahlweise' => 'UEB',
			'periode' => 1,
			'addressType' => 'anonym'
		];
	}

	public function testGivenValidRequest_tokensAreReturned() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();
			$factory->setTokenGenerator( new FixedTokenGenerator( self::SOME_TOKEN ) );

			$client->setServerParameter( 'HTTP_REFERER', 'https://en.wikipedia.org/wiki/Karla_Kennichnich' );
			$client->followRedirects( false );

			$client->request(
				'POST',
				'/donation/add',
				$this->newValidCreditCardInput()
			);

			$response = $client->getResponse()->getContent();

			$this->assertContains( self::SOME_TOKEN, $response );
		} );
	}

	public function testWhenTrackingCookieExists_valueIsPersisted() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();
			$client->getCookieJar()->set( new Cookie( 'spenden_tracking', 'test/blue' ) );

			$client->request(
				'POST',
				'/donation/add',
				$this->newComplementableFormInput()
			);

			$donation = $this->getDonationFromDatabase( $factory );
			$data = $donation->getDecodedData();

			$this->assertSame( 'test/blue', $data['tracking'] );
		} );
	}

	public function testWhenTrackableInputDataIsSubmitted_theyAreStoredInSession() {
		$this->createAppEnvironment( [], function ( Client $client, FunFunFactory $factory, Application $app ) {

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

	public function testWhenTolstojNovelIsPassed_isIsNotStoredInSession() {
		$this->createAppEnvironment( [], function ( Client $client, FunFunFactory $factory, Application $app ) {

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

	public function testWhenParameterIsOmitted_itIsNotStoredInSession() {
		$this->createAppEnvironment( [], function ( Client $client, FunFunFactory $factory, Application $app ) {

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

	public function testWhenInitiallyIntendedPaymentOptionsDifferFromActual_itIsReflectedInPiwikTrackingEvents() {
		$client = $this->createClient( [] );
		$client->request(
			'GET',
			'/',
			[
				'betrag' => '5.00',
				'zahlweise' => 'BEZ',
				'periode' => 12
			]
		);

		$client->request(
			'POST',
			'/donation/add',
			[
				'addressType' => 'anonym',
				'betrag' => '12,34',
				'periode' => '0',
				'zahlweise' => 'UEB'
			]
		);

		$responseContent = $client->getResponse()->getContent();
		$this->assertContains( 'BEZ/UEB', $responseContent );
		$this->assertContains( '5.00/12.34', $responseContent );
		$this->assertContains( '12/0', $responseContent );
	}

}
