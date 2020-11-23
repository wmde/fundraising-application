<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChange;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineEntities\Donation;
use WMDE\Fundraising\Frontend\App\Controllers\Donation\ShowDonationConfirmationController;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\Events\DonationCreated;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\PageViewTracker;
use WMDE\Fundraising\Frontend\Infrastructure\Translation\TranslatorInterface;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\BucketLoggerSpy;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;
use WMDE\Fundraising\Frontend\Tests\HttpKernelBrowser as Client;
use WMDE\Fundraising\PaymentContext\DataAccess\Sofort\Transfer\Client as SofortClient;
use WMDE\Fundraising\PaymentContext\DataAccess\Sofort\Transfer\Response as SofortResponse;
use WMDE\PsrLogTestDoubles\LoggerSpy;

/**
 * @license GPL-2.0-or-later
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 *
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Donation\AddDonationController
 * @requires extension konto_check
 */
class AddDonationRouteTest extends WebRouteTestCase {

	private const SOME_TOKEN = 'SomeToken';

	private const ADD_DONATION_PATH = '/donation/add';

	public function testGivenValidRequest_donationGetsPersisted(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
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

	public function testWhenDonationGetsPersisted_timestampIsStoredInCookie(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$client->followRedirects( true );
			$client->request(
				'POST',
				'/donation/add',
				$this->newValidFormInput()
			);

			$cookie = $client->getCookieJar()->get( 'donation_timestamp' );
			$this->assertNotNull( $cookie );
			$donationTimestamp = new \DateTime( $cookie->getValue() );
			$this->assertEqualsWithDelta( time(), $donationTimestamp->getTimestamp(), 5.0, 'Timestamp should be not more than 5 seconds old' );
		} );
	}

	public function testWhenMultipleDonationFormSubmissions_requestGetsRejected(): void {
		$client = $this->createClient();
		$client->getCookieJar()->set(
			new Cookie( FunFunFactory::DONATION_RATE_LIMIT_COOKIE_NAME, $this->getPastTimestamp() )
		);

		$client->request(
			'POST',
			'/donation/add',
			$this->newValidFormInput()
		);

		$this->assertStringContainsString( 'donation_rejected_limit', $client->getResponse()->getContent() );
	}

	public function testWhenMultipleDonationsInAccordanceToTimeLimit_requestIsNotRejected(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$client->getCookieJar()->set(
				new Cookie(
					FunFunFactory::DONATION_RATE_LIMIT_COOKIE_NAME,
					$this->getPastTimestamp( 'PT35M' )
				)
			);

			$client->request(
				'POST',
				'/donation/add',
				$this->newValidFormInput()
			);

			$this->assertStringNotContainsString( 'donation_rejected_limit', $client->getResponse()->getContent() );
		} );
	}

	private function getPastTimestamp( string $interval = 'PT10S' ): string {
		return ( new \DateTime() )->sub( new \DateInterval( $interval ) )->format( 'Y-m-d H:i:s' );
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
		$this->createEnvironment( [ 'skin' => 'laika' ], function ( Client $client, FunFunFactory $factory ): void {
			$client->request(
				'POST',
				'/donation/add',
				$this->newValidFormInput()
			);
			$client->followRedirect();

			$applicationVars = $this->getDataApplicationVars( $client->getCrawler() );

			$this->assertObjectHasAttribute( 'donation', $applicationVars );
			$this->assertSame( 5.51, $applicationVars->donation->amount );
			$this->assertSame( 0, $applicationVars->donation->interval );
			$this->assertSame( 'BEZ', $applicationVars->donation->paymentType );
			$this->assertTrue( $applicationVars->donation->optsIntoNewsletter );
			$this->assertTrue( $applicationVars->donation->optsIntoDonationReceipt );

			$this->assertObjectHasAttribute( 'bankData', $applicationVars );
			$this->assertSame( 'DE12500105170648489890', $applicationVars->bankData->iban );
			$this->assertSame( 'INGDDEFFXXX', $applicationVars->bankData->bic );
			$this->assertSame( 'ING-DiBa', $applicationVars->bankData->bankname );

			$this->assertObjectHasAttribute( 'address', $applicationVars );
			$this->assertSame( 'Prof. Dr. Karla Kennichnich', $applicationVars->address->fullName );
			$this->assertSame( 'Lehmgasse 12', $applicationVars->address->streetAddress );
			$this->assertSame( '12345', $applicationVars->address->postalCode );
			$this->assertSame( 'Einort', $applicationVars->address->city );
			$this->assertSame( 'DE', $applicationVars->address->countryCode );
			$this->assertSame( 'karla@kennichnich.de', $applicationVars->address->email );
		} );
	}

	public function testGivenValidBankTransferRequest_donationGetsPersisted(): void {
		/**
		 * @var FunFunFactory
		 */
		$factory = null;

		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
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
		} );
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
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
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
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$client->followRedirects( false );

			$client->request(
				'POST',
				'/donation/add',
				$this->newFrenchDonorFormInput()
			);

			$donation = $this->getDonationFromDatabase( $factory );
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
		} );
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

	private function getDonationFromDatabase( FunFunFactory $factory ): Donation {
		$donationRepo = $factory->getEntityManager()->getRepository( Donation::class );
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
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$translator = $this->createMock( TranslatorInterface::class );
			$translator->expects( $this->once() )
				->method( 'trans' )
				->with( 'paypal_item_name_donation' )
				->willReturn( 'Ihre Spende' );
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
		} );
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
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$response = new SofortResponse();
			$response->setPaymentUrl( 'https://bankingpin.please' );

			/** @var SofortClient&MockObject $sofortClient */
			$sofortClient = $this->createMock( SofortClient::class );
			$sofortClient
				->method( 'get' )
				->willReturn( $response );
			$factory->setSofortClient( $sofortClient );

			$client->followRedirects( false );
			$client->request(
				'POST',
				'/donation/add',
				$this->newValidSofortInput()
			);

			$donation = $this->getDonationFromDatabase( $factory );

			$this->assertSame( 'X', $donation->getStatus() );
			$this->assertMatchesRegularExpression( '/^(XR)-[ACDEFKLMNPRTWXYZ349]{3}-[ACDEFKLMNPRTWXYZ349]{3}-[ACDEFKLMNPRTWXYZ349]/', $donation->getBankTransferCode() );

			$this->assertTrue( $client->getResponse()->isRedirect( 'https://bankingpin.please' ) );
		} );
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

	public function testGivenInvalidRequest_formIsReloadedAndPrefilled(): void {
		$client = $this->createClient();
		$client->request(
			'POST',
			'/donation/add',
			$this->newInvalidFormInput()
		);

		$response = $client->getResponse()->getContent();

		$this->assertStringContainsString( 'Amount: 0,00', $response );
		$this->assertStringContainsString( 'Payment type: BEZ', $response );
		$this->assertStringContainsString( 'Interval: 3', $response );
		$this->assertStringContainsString( 'IBAN: DE12500105170648489890', $response );
		$this->assertStringContainsString( 'BIC: INGDDEFFXXX', $response );
		$this->assertStringContainsString( 'Bank name: ING-DiBa', $response );
		$this->assertStringContainsString( 'Address type: person', $response );
		$this->assertStringContainsString( 'Salutation: Frau', $response );
		$this->assertStringContainsString( 'Title: Prof. Dr.', $response );
		$this->assertStringContainsString( 'Company: ', $response );
		$this->assertStringContainsString( 'First name: Karla', $response );
		$this->assertStringContainsString( 'Last name: Kennichnich', $response );
		$this->assertStringContainsString( 'Street: Lehmgasse 12', $response );
		$this->assertStringContainsString( 'Postal code: 12345', $response );
		$this->assertStringContainsString( 'City: Einort', $response );
		$this->assertStringContainsString( 'Country code: DE', $response );
		$this->assertStringContainsString( 'Email address: karla@kennichnich.de', $response );
	}

	public function testGivenInvalidRequest_formStillContainsBannerTrackingData(): void {
		$client = $this->createClient();
		$client->request(
			'POST',
			'/donation/add',
			[
				'impCount' => 12,
				'bImpCount' => 3
			]
		);

		$response = $client->getResponse()->getContent();

		$this->assertStringContainsString( 'Impression Count: 12', $response );
		$this->assertStringContainsString( 'Banner Impression Count: 3', $response );
	}

	public function testGivenNegativeDonationAmount_formIsReloadedAndPrefilledWithZero(): void {
		$client = $this->createClient();

		$formValues = $this->newInvalidFormInput();
		$formValues['amount'] = '-5';

		$client->request(
			'POST',
			'/donation/add',
			$formValues
		);

		$response = $client->getResponse()->getContent();

		$this->assertStringContainsString( 'Amount: 0,00', $response );
	}

	public function testGivenInvalidRequest_errorsAreLogged(): void {
		$this->createEnvironment(
			[],
			function ( Client $client, FunFunFactory $factory ): void {
				$testHandler = new TestHandler();
				$factory->setLogger( new Logger( 'TestLogger', [ $testHandler ] ) );
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
		);
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

	public function testGivenInvalidAnonymousRequest_formIsReloadedAndPrefilled(): void {
		$client = $this->createClient();
		$client->request(
			'POST',
			'/donation/add',
			$this->newAnonymousFormInput()
		);

		$response = $client->getResponse()->getContent();

		$this->assertStringContainsString( 'Amount: 0', $response );
		$this->assertStringContainsString( 'Payment type: UEB', $response );
		$this->assertStringContainsString( 'Interval: 1', $response );
		$this->assertStringContainsString( 'Value of field "amount" violates rule: Amount too low', $response );
	}

	private function newAnonymousFormInput(): array {
		return [
			'amount' => '0',
			'paymentType' => 'UEB',
			'interval' => 1,
			'addressType' => 'anonym'
		];
	}

	public function testGivenValidRequest_tokensAreReturned(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$factory->setDonationTokenGenerator( new FixedTokenGenerator( self::SOME_TOKEN ) );

			$client->followRedirects( false );

			$client->request(
				'POST',
				'/donation/add',
				$this->newValidCreditCardInput()
			);

			$response = $client->getResponse()->getContent();

			$this->assertStringContainsString( self::SOME_TOKEN, $response );
		} );
	}

	public function testGivenValidRequest_clientIsRedirected(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$factory->setDonationTokenGenerator( new FixedTokenGenerator( self::SOME_TOKEN ) );
			$client->followRedirects( false );

			$client->request(
				'POST',
				'/donation/add',
				$this->newValidFormInput()
			);

			$this->assertTrue( $client->getResponse()->isRedirect() );
		} );
	}

	public function testWhenTrackingCookieExists_valueIsPersisted(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
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

	public function testWhenMobileTrackingIsRequested_piwikTrackerIsCalledForPaypalPayment(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$factory->setNullMessenger();
			$client->followRedirects( false );

			$tracker = $this->getMockBuilder( PageViewTracker::class )->disableOriginalConstructor()->getMock();
			$tracker->expects( $this->once() )
				->method( 'trackPaypalRedirection' )
				->with( 'test', 'gelb', '10.1.2.3' );
			$factory->setPageViewTracker( $tracker );

			$client->request(
				'POST',
				'/donation/add',
				$this->newValidMobilePayPalInput(),
				[],
				[ 'REMOTE_ADDR' => '10.1.2.3' ]
			);

			$client->getResponse();
		} );
	}

	private function newValidMobilePayPalInput(): array {
		return [
			'amount' => '1234',
			'paymentType' => 'PPL',
			'interval' => 3,
			'addressType' => 'anonym',
			'piwik_campaign' => 'test',
			'piwik_kwd' => 'gelb',
			// 'mbt' is the mobile tracking param that triggers a redirect to paypal for anonymous donations
			'mbt' => '1'
		];
	}

	public function testWhenMobileTrackingIsRequested_piwikTrackerIsNotCalledForNonExternalPayment(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$client->followRedirects( false );

			$tracker = $this->getMockBuilder( PageViewTracker::class )->disableOriginalConstructor()->getMock();
			$tracker->expects( $this->never() )
				->method( 'trackPaypalRedirection' );
			$factory->setPageViewTracker( $tracker );

			$client->request(
				'POST',
				'/donation/add',
				array_merge(
					$this->newValidBankTransferInput(),
					[ 'mbt' => '1' ]
				)
			);

			$client->getResponse();
		} );
	}

	public function testGivenCommasInStreetInput_donationGetsPersisted(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$client->setServerParameter( 'HTTP_REFERER', 'https://en.wikipedia.org/wiki/Karla_Kennichnich' );
			$client->followRedirects( false );

			$formInput = $this->newValidFormInput();
			$formInput['street'] = ',Lehmgasse, 12,';

			$client->request(
				'POST',
				'/donation/add',
				$formInput
			);

			$this->assertIsExpectedDonation( $this->getDonationFromDatabase( $factory ) );
		} );
	}

	public function testCookieFlagsSecureAndHttpOnlyAreSet(): void {
		$client = $this->createClient();
		$client->setServerParameter( 'HTTPS', true );
		$client->followRedirects( true );

		$client->request(
			'POST',
			'/donation/add',
			$this->newValidFormInput()
		);

		$cookieJar = $client->getCookieJar();
		$cookieJar->updateFromResponse( $client->getInternalResponse() );
		$cookie = $cookieJar->get( FunFunFactory::DONATION_RATE_LIMIT_COOKIE_NAME );
		$this->assertTrue( $cookie->isSecure() );
		$this->assertTrue( $cookie->isHttpOnly() );
	}

	public function testDonationReceiptOptOut_persistedInDonation(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$parameters = $this->newValidFormInput();
			$parameters['donationReceipt'] = '0';

			$client->request( Request::METHOD_POST, self::ADD_DONATION_PATH, $parameters );

			$this->assertFalse( $this->getDonationFromDatabase( $factory )->getDonationReceipt() );
		} );
	}

	public function testGivenValidRequest_bucketsAreLogged(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
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

	public function testGivenValidRequest_addressChangeRecordIsCreated(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$client->followRedirects( false );

			$client->request(
				'POST',
				'/donation/add',
				$this->newValidFormInput()
			);

			/** @var AddressChange[] $addressChanges */
			$addressChanges = $factory->getEntityManager()->getRepository( AddressChange::class )->findAll();
			$this->assertCount( 1, $addressChanges );
			$this->assertTrue( $addressChanges[0]->getExternalIdType() === AddressChange::EXTERNAL_ID_TYPE_DONATION );
			$this->assertTrue( $addressChanges[0]->isPersonalAddress() );
		} );
	}

	private function getDataApplicationVars( Crawler $crawler ): object {
		/** @var \DOMElement $appElement */
		$appElement = $crawler->filter( '#appdata' )->getNode( 0 );
		return json_decode( $appElement->getAttribute( 'data-application-vars' ) );
	}
}
