<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineEntities\Donation;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\Events\DonationCreated;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\BucketLoggerSpy;
use WMDE\Fundraising\Frontend\Tests\Fixtures\InMemoryTranslator;
use WMDE\Fundraising\Frontend\Tests\Fixtures\PayPalAPISpy;
use WMDE\Fundraising\PaymentContext\Services\PaymentUrlGenerator\Sofort\Response as SofortResponse;
use WMDE\Fundraising\PaymentContext\Services\PaymentUrlGenerator\Sofort\SofortClient;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Donation\AddDonationController
 * @requires extension konto_check
 */
class AddDonationRouteTest extends WebRouteTestCase {

	use GetApplicationVarsTrait;

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

		$this->assertTrue( $client->getResponse()->isOk() );
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
		$this->assertTrue( $donation->getDonorOptsIntoNewsletter() );

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

	/**
	 * @todo Remove this test when PayPal API integration is done
	 */
	public function testWhenRedirectingToPayPal_translatedItemNameIsPassed(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$factory->setLocale( 'de_DE' );

		if ( $factory->useLegacyPaypalAPI() ) {
			$this->canaryForRemovingLegacyPayPalURLGeneratorConfig();
			$this->markTestSkipped();
		}

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

	/**
	 * @dataProvider provideLocaleAndSubscriptionIDForPayPal
	 */
	public function testWhenRedirectingToPayPalLocaleDependantSubscriptionIdIsChosen( string $locale, string $expected ): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		// TODO remove if statement when paypal integration is done
		if ( $factory->useLegacyPaypalAPI() ) {
			$this->canaryForRemovingLegacyPayPalURLGeneratorConfig();
			$this->markTestSkipped();
		}
		$paypalApiSpy = new PayPalAPISpy();
		$factory->setPayPalAPI( $paypalApiSpy );

		$client->followRedirects( false );
		$client->request(
			'POST',
			'/donation/add?locale=' . $locale,
			$this->newValidPayPalInput()
		);

		$this->assertSame( $expected, $paypalApiSpy->lastCalledSubscriptionPlanId() );
	}

	/**
	 * @return iterable<array{string,string}>
	 */
	public static function provideLocaleAndSubscriptionIDForPayPal(): iterable {
		// subscription plan ids come from the file test/Data/files/paypal_api.yml)
		yield [ 'en_GB', 'P-4E8195' ];
		yield [ 'de_DE', 'P-5PB46799' ];
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

		$response = $client->getResponse();
		$this->assertTrue( $response->isRedirect() );
		$this->assertSame( 'https://bankingpin.please', $response->headers->get( 'Location' ) );
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

	public function testGivenValidRequest_clientIsRedirected(): void {
		$client = $this->createClient();
		$client->followRedirects( false );

		$client->request(
			'POST',
			'/donation/add',
			$this->newValidBankTransferInput()
		);

		$this->assertTrue( $client->getResponse()->isRedirect() );
	}

	public function testGivenValidRequest_redirectionUrlContainsAuthenticationTokens(): void {
		$client = $this->createClient();
		$client->followRedirects( false );

		$client->request(
			'POST',
			'/donation/add',
			$this->newValidBankTransferInput()
		);

		$response = $client->getResponse();
		$redirectUrl = $response->headers->get( 'Location' );
		$this->assertMatchesRegularExpression( '/accessToken=[0-9a-f]+/i', $redirectUrl );
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
		$client = $this->createClient();
		$factory = $this->getFactory();
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
	}

	public function testGivenRequestForBankDataPayment_redirectUrlHasBucketParameters(): void {
		$client = $this->createClient();
		$client->followRedirects( false );

		$client->request(
			'POST',
			// see campaigns.test.yml for URL parameters
			'/donation/add?pfu=1',
			$this->newValidBankTransferInput()
		);

		$response = $client->getResponse();
		$this->assertTrue( $response->isRedirect() );
		$this->assertStringContainsString( 'pfu=1', $response->headers->get( 'Location' ) );
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

	/**
	 * We expect the transition to teh PayPal API to be done in mid-2024
	 * Ticket: https://phabricator.wikimedia.org/T329159
	 *
	 * @todo remove when ticket is done
	 */
	public function canaryForRemovingLegacyPayPalURLGeneratorConfig(): void {
		if ( time() > strtotime( '2024-08-30' ) ) {
			$this->fail();
		}
	}

}
