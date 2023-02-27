<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\AbstractBrowser as Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineEntities\Donation;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\Events\DonationCreated;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\BucketLoggerSpy;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;
use WMDE\Fundraising\Frontend\Tests\Fixtures\InMemoryTranslator;
use WMDE\Fundraising\PaymentContext\DataAccess\Sofort\Transfer\Response as SofortResponse;
use WMDE\Fundraising\PaymentContext\DataAccess\Sofort\Transfer\SofortClient;

/**
 * @license GPL-2.0-or-later
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 *
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Donation\AddDonationController
 * @requires extension konto_check
 */
class AddDonationRouteTest extends WebRouteTestCase {

	use GetApplicationVarsTrait;

	private const SOME_TOKEN = 'SomeToken';

	private const ADD_DONATION_PATH = '/donation/add';

	public function testGivenValidRequest_donationGetsPersisted(): void {
		$client = $this->createClient();
		$client->setServerParameter( 'HTTP_REFERER', 'https://en.wikipedia.org/wiki/Karla_Kennichnich' );
		$client->followRedirects( false );

		$client->request(
			'POST',
			'/donation/add',
			$this->newValidFormInput()
		);

		$this->assertIsExpectedDonation( $this->getDonationFromDatabase() );
	}

	public function testWhenMultipleDonationFormSubmissions_requestGetsRejected(): void {
		$client = $this->createClient();
		$this->prepareSessionValues( [ FunFunFactory::DONATION_RATE_LIMIT_SESSION_KEY => new \DateTimeImmutable() ] );

		$client->request(
			'POST',
			'/donation/add',
			$this->newValidFormInput()
		);

		$this->assertStringContainsString( 'donation_rejected_limit', $client->getResponse()->getContent() );
	}

	public function testWhenMultipleDonationsInAccordanceToTimeLimit_requestIsNotRejected(): void {
		$client = $this->createClient();
		$someMinutesAgo = ( new \DateTimeImmutable() )->sub( new \DateInterval( 'PT35M' ) );
		$this->prepareSessionValues( [ FunFunFactory::DONATION_RATE_LIMIT_SESSION_KEY => $someMinutesAgo ] );

		$client->request(
			'POST',
			'/donation/add',
			$this->newValidFormInput()
		);

		$this->assertStringNotContainsString( 'donation_rejected_limit', $client->getResponse()->getContent() );
	}

	private function newValidFormInput(): array {
		return [
			'amount' => '551',
			'paymentType' => 'BEZ',
			'interval' => 0,
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

	private function assertIsExpectedDonation( Donation $donation ): void {
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
		$this->assertSame( '', $data['layout'] );
		$this->assertSame( '', $data['color'] );
		$this->assertSame( '', $data['skin'] );
		$this->assertSame( '', $data['source'] );
		$this->assertSame( 'N', $donation->getStatus() );
		$this->assertTrue( $donation->getDonorOptsIntoNewsletter() );
		$this->assertTrue( $donation->getDonationReceipt() );
	}

	public function testGivenValidRequest_confirmationPageContainsEnteredData(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();
		if ( $client instanceof KernelBrowser ) {
			$client->disableReboot();
		}
		$client->followRedirects( true );
		$client->request(
			'POST',
			'/donation/add',
			$this->newValidFormInput()
		);

		$applicationVars = $this->getDataApplicationVars( $client->getCrawler() );

		$this->assertTrue( property_exists( $applicationVars, 'donation' ), 'applicationVars should have a "donation" property' );
		$this->assertSame( 5.51, $applicationVars->donation->amount );
		$this->assertSame( 0, $applicationVars->donation->interval );
		$this->assertSame( 'BEZ', $applicationVars->donation->paymentType );
		$this->assertTrue( $applicationVars->donation->newsletter );
		$this->assertTrue( $applicationVars->donation->receipt );

		$this->assertTrue( property_exists( $applicationVars, 'bankData' ), 'applicationVars should have a "bankData" property' );
		$this->assertSame( 'DE12500105170648489890', $applicationVars->bankData->iban );
		$this->assertSame( 'INGDDEFFXXX', $applicationVars->bankData->bic );
		$this->assertSame( 'ING-DiBa', $applicationVars->bankData->bankname );

		$this->assertTrue( property_exists( $applicationVars, 'address' ), 'applicationVars should have a "address" property' );
		$this->assertSame( 'Prof. Dr. Karla Kennichnich', $applicationVars->address->fullName );
		$this->assertSame( 'Lehmgasse 12', $applicationVars->address->street );
		$this->assertSame( '12345', $applicationVars->address->postcode );
		$this->assertSame( 'Einort', $applicationVars->address->city );
		$this->assertSame( 'DE', $applicationVars->address->country );
		$this->assertSame( 'karla@kennichnich.de', $applicationVars->address->email );
	}

	public function testGivenValidBankTransferRequest_donationGetsPersisted(): void {
		$client = $this->createClient();
		$client->setServerParameter( 'HTTP_REFERER', 'https://en.wikipedia.org/wiki/Karla_Kennichnich' );
		$client->followRedirects( false );

		$client->request(
			'POST',
			'/donation/add',
			$this->newValidBankTransferInput()
		);

		$donation = $this->getDonationFromDatabase();

		$data = $donation->getDecodedData();
		$this->assertSame( '12.34', $donation->getAmount() );
		$this->assertSame( 'UEB', $donation->getPaymentType() );
		$this->assertSame( 0, $donation->getPaymentIntervalInMonths() );
		$this->assertSame( 'person', $data['adresstyp'] );
		$this->assertSame( 'Frau', $data['anrede'] );
		$this->assertSame( 'Prof. Dr.', $data['titel'] );
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
		$this->assertSame( '', $data['layout'] );
		$this->assertSame( '', $data['color'] );
		$this->assertSame( '', $data['skin'] );
		$this->assertSame( '', $data['source'] );
		$this->assertSame( true, $donation->getDonorOptsIntoNewsletter() );

		$this->assertSame( 'Z', $donation->getStatus() );
		$this->assertMatchesRegularExpression( '/^(XW)-[ACDEFKLMNPRTWXYZ349]{3}-[ACDEFKLMNPRTWXYZ349]{3}-[ACDEFKLMNPRTWXYZ349]/', $donation->getBankTransferCode() );
	}

	private function newValidBankTransferInput(): array {
		return [
			'amount' => '1234',
			'paymentType' => 'UEB',
			'interval' => 0,
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

	public function testGivenComplementableBankData_donationStillGetsPersisted(): void {
		$client = $this->createClient();
		$client->followRedirects( false );

		$client->request(
			'POST',
			'/donation/add',
			$this->newComplementableFormInput()
		);

		$donation = $this->getDonationFromDatabase();
		$data = $donation->getDecodedData();
		$this->assertSame( 'DE12500105170648489890', $data['iban'] );
		$this->assertSame( 'INGDDEFFXXX', $data['bic'] );
		$this->assertSame( '0648489890', $data['konto'] );
		$this->assertSame( '50010517', $data['blz'] );
		$this->assertSame( 'ING-DiBa', $data['bankname'] );
	}

	private function newComplementableFormInput(): array {
		return [
			'amount' => '551',
			'paymentType' => 'BEZ',
			'interval' => 0,
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

	public function testGivenNonGermanDonor_donationGetsPersisted(): void {
		$client = $this->createClient();
		$client->followRedirects( false );

		$client->request(
			'POST',
			'/donation/add',
			$this->newFrenchDonorFormInput()
		);

		$donation = $this->getDonationFromDatabase();
		$data = $donation->getDecodedData();
		$this->assertSame( 'Claire', $data['vorname'] );
		$this->assertSame( 'Jennesaispas', $data['nachname'] );
		$this->assertSame( 'Claire Jennesaispas', $donation->getDonorFullName() );
		$this->assertSame( 'Ruelle Argile 12', $data['strasse'] );
		$this->assertSame( '12345', $data['plz'] );
		$this->assertSame( 'Unlieu', $data['ort'] );
		$this->assertSame( 'Unlieu', $donation->getDonorCity() );
		$this->assertSame( 'FR', $data['country'] );
		$this->assertSame( 'claire@jennesaispas.fr', $data['email'] );
		$this->assertSame( 'claire@jennesaispas.fr', $donation->getDonorEmail() );
		$this->assertSame( 'FR7630066100410001057380116', $data['iban'] );
		$this->assertSame( '', $data['bic'] );
		$this->assertSame( '', $data['konto'] );
		$this->assertSame( '', $data['blz'] );
		$this->assertSame( '', $data['bankname'] );
	}

	private function newFrenchDonorFormInput(): array {
		return [
			'amount' => '551',
			'paymentType' => 'BEZ',
			'interval' => 0,
			'iban' => 'FR7630066100410001057380116',
			'addressType' => 'person',
			'salutation' => 'Frau',
			'title' => '',
			'firstName' => 'Claire',
			'lastName' => 'Jennesaispas',
			'street' => 'Ruelle Argile 12',
			'postcode' => '12345',
			'city' => 'Unlieu',
			'country' => 'FR',
			'email' => 'claire@jennesaispas.fr',
		];
	}

	private function getDonationFromDatabase(): Donation {
		$donationRepo = $this->getFactory()->getEntityManager()->getRepository( Donation::class );
		$donation = $donationRepo->find( 1 );
		$this->assertInstanceOf( Donation::class, $donation );
		return $donation;
	}

	public function testGivenValidPayPalData_redirectsToPayPal(): void {
		$client = $this->createClient();
		$client->followRedirects( false );

		$client->request(
			'POST',
			'/donation/add',
			$this->newValidPayPalInput()
		);

		$response = $client->getResponse();
		$this->assertSame( Response::HTTP_FOUND, $response->getStatusCode() );
		$this->assertStringContainsString( 'sandbox.paypal.com', $response->getContent() );
	}

	public function testWhenRedirectingToPayPal_translatedItemNameIsPassed(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$translator = new InMemoryTranslator( [
				'paypal_item_name_donation' => 'Ihre Spende',
				'payment_interval_3' => 'vierteljährlich',
		] );
		$factory->setPaymentProviderItemsTranslator( $translator );

		$client->followRedirects( false );

		$client->request(
			'POST',
			'/donation/add',
			$this->newValidPayPalInput()
		);

		$response = $client->getResponse();
		$this->assertSame( Response::HTTP_FOUND, $response->getStatusCode() );
		$this->assertStringContainsString( 'item_name=Ihre+Spende', $response->getContent() );
	}

	private function newValidPayPalInput(): array {
		return [
			'amount' => '1234',
			'paymentType' => 'PPL',
			'interval' => 3,
			'addressType' => 'anonym',
		];
	}

	public function testGivenValidCreditCardData_redirectsToPaymentProvider(): void {
		$client = $this->createClient();
		$client->request(
			'POST',
			'/donation/add',
			$this->newValidCreditCardInput()
		);

		$response = $client->getResponse();
		$this->assertSame( Response::HTTP_FOUND, $response->getStatusCode() );
		$this->assertTrue( $response->isRedirect() );
		$this->assertStringContainsString( 'amount=1234', $response->headers->get( 'Location' ) );
		$this->assertStringContainsString( 'thatother.paymentprovider.com', $response->headers->get( 'Location' ) );
	}

	public function testValidSofortInput_savesDonationAndRedirectsTo3rdPartyPage(): void {
		$client = $this->createClient();
		$response = new SofortResponse();
		$response->setPaymentUrl( 'https://bankingpin.please' );

		/** @var SofortClient&MockObject $sofortClient */
		$sofortClient = $this->createMock( SofortClient::class );
		$sofortClient
			->method( 'get' )
			->willReturn( $response );
		$this->getFactory()->setSofortClient( $sofortClient );

		$client->followRedirects( false );
		$client->request(
			'POST',
			'/donation/add',
			$this->newValidSofortInput()
		);

		$donation = $this->getDonationFromDatabase();

		$this->assertSame( 'X', $donation->getStatus() );
		$this->assertMatchesRegularExpression( '/^(XR)-[ACDEFKLMNPRTWXYZ349]{3}-[ACDEFKLMNPRTWXYZ349]{3}-[ACDEFKLMNPRTWXYZ349]/', $donation->getBankTransferCode() );

		$this->assertTrue( $client->getResponse()->isRedirect( 'https://bankingpin.please' ) );
	}

	private function newValidCreditCardInput(): array {
		return [
			'amount' => '1234',
			'paymentType' => 'MCP',
			'interval' => 3,
			'addressType' => 'anonym',
		];
	}

	private function newValidSofortInput(): array {
		return [
			'amount' => '10000',
			'paymentType' => 'SUB',
			'interval' => 0,
			'addressType' => 'anonym',
		];
	}

	public function testGivenInvalidRequest_genericErrorMessageIsDisplayed(): void {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/donation/add',
			$this->newInvalidFormInput()
		);

		$response = $client->getResponse()->getContent();
		$this->assertStringContainsString( 'Internal Error: Creating a donation was not successful.', $response );
	}

	public function testGivenInvalidRequest_errorsAreLogged(): void {
		$client = $this->createClient();
		$testHandler = new TestHandler();
		$this->getFactory()->setLogger( new Logger( 'TestLogger', [ $testHandler ] ) );

		$client->request(
			'POST',
			'/donation/add',
			$this->newInvalidFormInput()
		);

		$this->assertTrue( $testHandler->hasWarningRecords() );
		foreach ( $testHandler->getRecords() as $record ) {
			$this->assertEquals( 'Unexpected server-side form validation errors.', $record['message'] );
		}
	}

	private function newInvalidFormInput(): array {
		return [
			'amount' => '0',
			'paymentType' => 'BEZ',
			'interval' => 3,
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

	public function testGivenValidRequest_tokensAreReturned(): void {
		$client = $this->createClient();
		$this->getFactory()->setDonationTokenGenerator( new FixedTokenGenerator( self::SOME_TOKEN ) );

		$client->followRedirects( false );

		$client->request(
			'POST',
			'/donation/add',
			$this->newValidCreditCardInput()
		);

		$response = $client->getResponse()->getContent();

		$this->assertStringContainsString( self::SOME_TOKEN, $response );
	}

	public function testGivenValidRequest_clientIsRedirected(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$factory->setDonationTokenGenerator( new FixedTokenGenerator( self::SOME_TOKEN ) );
		$client->followRedirects( false );

		$client->request(
			'POST',
			'/donation/add',
			$this->newValidFormInput()
		);

		$this->assertTrue( $client->getResponse()->isRedirect() );
	}

	public function testGivenCommasInStreetInput_donationGetsPersisted(): void {
		$client = $this->createClient();
		$client->setServerParameter( 'HTTP_REFERER', 'https://en.wikipedia.org/wiki/Karla_Kennichnich' );
		$client->followRedirects( false );

		$formInput = $this->newValidFormInput();
		$formInput['street'] = ',Lehmgasse, 12,';

		$client->request(
			'POST',
			'/donation/add',
			$formInput
		);

		$this->assertIsExpectedDonation( $this->getDonationFromDatabase() );
	}

	public function testDonationReceiptOptOut_persistedInDonation(): void {
		$client = $this->createClient();
		$parameters = $this->newValidFormInput();
		$parameters['donationReceipt'] = '0';

		$client->request( Request::METHOD_POST, self::ADD_DONATION_PATH, $parameters );

		$this->assertFalse( $this->getDonationFromDatabase()->getDonationReceipt() );
	}

	public function testGivenValidRequest_bucketsAreLogged(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$bucketLogger = new BucketLoggerSpy();
			$factory->setBucketLogger( $bucketLogger );
			$client->followRedirects( false );

			$client->request(
				'POST',
				'/donation/add',
				$this->newValidFormInput()
			);

			$this->assertSame( 1, $bucketLogger->getEventCount() );
			$this->assertInstanceOf( DonationCreated::class, $bucketLogger->getFirstEvent() );
		} );
	}

	public function testGivenAnonymousDonorWithBankTransfer_genericErrorMessageIsDisplayed(): void {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/donation/add',
			$this->newFormInputAnonymousDonorWithBankTransfer()
		);

		$response = $client->getResponse();
		$this->assertFalse( $response->isRedirect(), 'Response should not redirect to success page' );
		$this->assertStringContainsString( 'Internal Error: Creating a donation was not successful.', $response->getContent() );
	}

	private function newFormInputAnonymousDonorWithBankTransfer(): array {
		return [
			'amount' => '551',
			'paymentType' => 'BEZ',
			'interval' => 0,
			'iban' => 'DE12500105170648489890',
			'bic' => 'INGDDEFFXXX',
			'addressType' => 'anonym',
			'piwik_campaign' => 'test',
			'piwik_kwd' => 'gelb',
			'impCount' => '3',
			'bImpCount' => '1',
		];
	}

}
