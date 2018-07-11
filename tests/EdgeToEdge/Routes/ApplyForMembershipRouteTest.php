<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Entities\MembershipApplication;
use WMDE\Fundraising\Frontend\App\RouteHandlers\ApplyForMembershipHandler;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\MembershipContext\Tests\Data\ValidMembershipApplication;
use WMDE\Fundraising\MembershipContext\Tests\Fixtures\FixedMembershipTokenGenerator;
use WMDE\Fundraising\PaymentContext\Domain\Model\PayPalData;
use WMDE\Fundraising\PaymentContext\Domain\PaymentDelayCalculator;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedPaymentDelayCalculator;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 *
 * @requires extension konto_check
 */
class ApplyForMembershipRouteTest extends WebRouteTestCase {

	private const FIXED_TOKEN = 'fixed_token';
	private const FIRST_PAYMENT_DATE = '2017-09-21';

	private const APPLY_FOR_MEMBERSHIP_PATH = 'apply-for-membership';

	public function testGivenGetRequestMembership_formIsShown(): void {
		$client = $this->createClient();

		$crawler = $client->request( 'GET', 'apply-for-membership' );

		$this->assertCount(
			1,
			$crawler->filter( 'form#memForm[method="POST"][action="/apply-for-membership"]' )
		);
		$this->assertCount(
			1,
			$crawler->filter( 'input[name="showMembershipTypeOption"][type="hidden"][value="true"]' )
		);
	}

	public function testGivenGetRequestSustainingMembership_formIsShown(): void {
		$client = $this->createClient();

		$crawler = $client->request( 'GET', 'apply-for-membership', ['type' => 'sustaining'] );

		$this->assertCount(
			1,
			$crawler->filter( 'form#memForm[method="POST"][action="/apply-for-membership"]' )
		);
		$this->assertCount(
			1,
			$crawler->filter( 'input[name="showMembershipTypeOption"][type="hidden"][value="false"]' )
		);
	}

	public function testGivenRequestWithDonationIdAndCorrespondingAccessCode_successResponseWithInitialFormValuesIsReturned(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$factory->setDonationTokenGenerator( new FixedTokenGenerator( '4711abc' ) );
			$factory->getDonationRepository()->storeDonation( ValidDonation::newDirectDebitDonation() );

			$httpParameters = [
				'donationId' => 1,
				'donationAccessToken' => '4711abc'
			];

			$client->request( Request::METHOD_GET, self::APPLY_FOR_MEMBERSHIP_PATH, $httpParameters );

			$this->assertInitialFormValues(
				[
					'addressType' => 'person',
					'salutation' => 'nyan',
					'title' => 'nyan',
					'firstName' => 'Jeroen',
					'lastName' => 'De Dauw',
					'companyName' => '',
					'street' => 'Nyan Street',
					'postcode' => '1234',
					'city' => 'Berlin',
					'country' => 'DE',
					'email' => 'foo@bar.baz',
					'iban' => 'DE12500105170648489890',
					'bic' => 'INGDDEFFXXX',
					'accountNumber' => '0648489890',
					'bankCode' => '50010517',
					'bankname' => 'ING-DiBa',
					'paymentType' => 'BEZ'
				],
				$client
			);
		} );
	}

	private function newValidHttpParameters(): array {
		return [
			'membership_type' => ValidMembershipApplication::MEMBERSHIP_TYPE,

			'adresstyp' => 'person',
			'anrede' => ValidMembershipApplication::APPLICANT_SALUTATION,
			'titel' => ValidMembershipApplication::APPLICANT_TITLE,
			'vorname' => ValidMembershipApplication::APPLICANT_FIRST_NAME,
			'nachname' => ValidMembershipApplication::APPLICANT_LAST_NAME,
			'firma' => '',

			'strasse' => ValidMembershipApplication::APPLICANT_STREET_ADDRESS,
			'postcode' => ValidMembershipApplication::APPLICANT_POSTAL_CODE,
			'ort' => ValidMembershipApplication::APPLICANT_CITY,
			'country' => ValidMembershipApplication::APPLICANT_COUNTRY_CODE,

			'email' => ValidMembershipApplication::APPLICANT_EMAIL_ADDRESS,
			'phone' => ValidMembershipApplication::APPLICANT_PHONE_NUMBER,
			'dob' => ValidMembershipApplication::APPLICANT_DATE_OF_BIRTH,

			'payment_type' => (string)ValidMembershipApplication::PAYMENT_TYPE_DIRECT_DEBIT,
			'membership_fee_interval' => (string)ValidMembershipApplication::PAYMENT_PERIOD_IN_MONTHS,
			'membership_fee' => (string)ValidMembershipApplication::PAYMENT_AMOUNT_IN_EURO, // TODO: change to localized

			'bank_name' => ValidMembershipApplication::PAYMENT_BANK_NAME,
			'iban' => ValidMembershipApplication::PAYMENT_IBAN,
			'bic' => ValidMembershipApplication::PAYMENT_BIC,
			'account_number' => ValidMembershipApplication::PAYMENT_BANK_ACCOUNT,
			'bank_code' => ValidMembershipApplication::PAYMENT_BANK_CODE,
		];
	}

	public function testGivenRequestWithInsufficientAmount_failureResponseIsReturned(): void {
		$client = $this->createClient();

		$httpParameters = $this->newValidHttpParameters();
		$httpParameters['membership_fee'] = '1.00'; // TODO: change to localized

		$client->request( 'POST', 'apply-for-membership', $httpParameters );

		$this->assertInitialFormValues(
			[
				'addressType' => 'person',
				'salutation' => 'Herr',
				'title' => '',
				'firstName' => 'Potato',
				'lastName' => 'The Great',
				'companyName' => '',
				'street' => 'Nyan street',
				'postcode' => '1234',
				'city' => 'Berlin',
				'country' => 'DE',
				'email' => 'jeroendedauw@gmail.com',
				'iban' => 'DE12500105170648489890',
				'bic' => 'INGDDEFFXXX',
				'accountNumber' => '0648489890',
				'bankCode' => '50010517',
				'bankname' => 'ING-DiBa',
				'paymentType' => 'BEZ'
			],
			$client
		);
	}

	public function testFlagForShowingMembershipTypeOptionGetsPassedAround(): void {
		$client = $this->createClient();

		$httpParameters = $this->newValidHttpParameters();
		$httpParameters['membership_fee'] = '0';
		$httpParameters['showMembershipTypeOption'] = 'true';

		$crawler = $client->request(
			'POST',
			'apply-for-membership',
			$httpParameters
		);

		$this->assertCount(
			1,
			$crawler->filter( 'input[type="hidden"][name="showMembershipTypeOption"][value="true"]' )
		);
	}

	public function testGivenValidRequest_applicationIsPersisted(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$factory->setPaymentDelayCalculator( $this->newFixedPaymentDelayCalculator() );

			$client->request(
				'POST',
				'apply-for-membership',
				$this->newValidHttpParameters()
			);

			$application = $factory->getMembershipApplicationRepository()->getApplicationById( 1 );

			$this->assertNotNull( $application );

			$expectedApplication = ValidMembershipApplication::newAutoConfirmedDomainEntity();
			$expectedApplication->assignId( 1 );

			$this->assertEquals( $expectedApplication, $application );
		} );
	}

	public function testGivenValidRequest_confirmationPageContainsCancellationParameters(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$factory->setMembershipTokenGenerator( new FixedMembershipTokenGenerator( self::FIXED_TOKEN ) );

			$client->request(
				'POST',
				'apply-for-membership',
				$this->newValidHttpParameters()
			);

			$responseContent = $client->getResponse()->getContent();

			$this->assertContains( 'id=1', $responseContent );
			$this->assertContains( 'accessToken=' . self::FIXED_TOKEN, $responseContent );
		} );
	}

	public function testGivenValidRequest_requestIsRedirected(): void {
		$client = $this->createClient();
		$client->followRedirects( false );

		$client->request(
			'POST',
			'apply-for-membership',
			$this->newValidHttpParameters()
		);

		$response = $client->getResponse();
		$this->assertTrue( $response->isRedirect() );
		$this->assertContains( 'show-membership-confirmation', $response->headers->get( 'Location' ) );
	}

	public function testWhenApplicationGetsPersisted_timestampIsStoredInCookie(): void {
		$client = $this->createClient();
		$client->request(
			'POST',
			'/apply-for-membership',
			$this->newValidHttpParameters()
		);

		$cookie = $client->getCookieJar()->get( 'memapp_timestamp' );
		$this->assertNotNull( $cookie );
		$donationTimestamp = new \DateTime( $cookie->getValue() );
		$this->assertEquals( time(), $donationTimestamp->getTimestamp(), 'Timestamp should be not more than 5 seconds old', 5.0 );
	}

	public function testWhenMultipleMembershipFormSubmissions_requestGetsRejected(): void {
		$client = $this->createClient();
		$client->getCookieJar()->set( new Cookie( 'memapp_timestamp', $this->getPastTimestamp() ) );

		$client->request(
			'POST',
			'/apply-for-membership',
			$this->newValidHttpParameters()
		);

		$this->assertContains( 'membership_application_rejected_limit', $client->getResponse()->getContent() );
	}

	public function testWhenMultipleMembershipInAccordanceToTimeLimit_isNotRejected(): void {
		$client = $this->createClient();
		$client->getCookieJar()->set( new Cookie( 'memapp_timestamp', $this->getPastTimestamp( 'PT12M' ) ) );

		$client->request(
			'POST',
			'/apply-for-membership',
			$this->newValidHttpParameters()
		);

		$this->assertNotContains( 'membership_application_rejected_limit', $client->getResponse()->getContent() );
	}

	private function getPastTimestamp( string $interval = 'PT10S' ): string {
		return ( new \DateTime() )->sub( new \DateInterval( $interval ) )->format( 'Y-m-d H:i:s' );
	}

	public function testWhenTrackingCookieExists_valueIsPersisted(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$client->getCookieJar()->set( new Cookie( 'spenden_tracking', 'test/blue' ) );

			$client->request(
				'POST',
				'/apply-for-membership',
				$this->newValidHttpParameters()
			);

			$application = $this->getApplicationFromDatabase( $factory );
			$this->assertSame( 'test/blue', $application->getTracking() );
		} );
	}

	private function getApplicationFromDatabase( FunFunFactory $factory ): MembershipApplication {
		$repository = $factory->getEntityManager()->getRepository( MembershipApplication::class );
		$application = $repository->find( 1 );
		$this->assertInstanceOf( MembershipApplication::class, $application );
		return $application;
	}

	public function testGivenValidRequestUsingPayPal_applicationIsPersisted(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$factory->setPaymentDelayCalculator( $this->newFixedPaymentDelayCalculator() );

			$client->request(
				'POST',
				'apply-for-membership',
				$this->newValidHttpParametersUsingPayPal()
			);

			$application = $factory->getMembershipApplicationRepository()->getApplicationById( 1 );

			$this->assertNotNull( $application );

			$payPalData = new PayPalData();
			$payPalData->setFirstPaymentDate( self::FIRST_PAYMENT_DATE );
			$payPalData->freeze();

			$expectedApplication = ValidMembershipApplication::newDomainEntityUsingPayPal( $payPalData );
			$expectedApplication->assignId( 1 );

			$this->assertEquals( $expectedApplication, $application );
		} );
	}

	private function newValidHttpParametersUsingPayPal(): array {
		return [
			'membership_type' => ValidMembershipApplication::MEMBERSHIP_TYPE,

			'adresstyp' => 'person',
			'anrede' => ValidMembershipApplication::APPLICANT_SALUTATION,
			'titel' => ValidMembershipApplication::APPLICANT_TITLE,
			'vorname' => ValidMembershipApplication::APPLICANT_FIRST_NAME,
			'nachname' => ValidMembershipApplication::APPLICANT_LAST_NAME,
			'firma' => '',

			'strasse' => ValidMembershipApplication::APPLICANT_STREET_ADDRESS,
			'postcode' => ValidMembershipApplication::APPLICANT_POSTAL_CODE,
			'ort' => ValidMembershipApplication::APPLICANT_CITY,
			'country' => ValidMembershipApplication::APPLICANT_COUNTRY_CODE,

			'email' => ValidMembershipApplication::APPLICANT_EMAIL_ADDRESS,
			'phone' => ValidMembershipApplication::APPLICANT_PHONE_NUMBER,
			'dob' => ValidMembershipApplication::APPLICANT_DATE_OF_BIRTH,

			'payment_type' => (string)ValidMembershipApplication::PAYMENT_TYPE_PAYPAL,
			'membership_fee_interval' => (string)ValidMembershipApplication::PAYMENT_PERIOD_IN_MONTHS,
			'membership_fee' => (string)ValidMembershipApplication::PAYMENT_AMOUNT_IN_EURO, // TODO: change to localized
		];
	}

	public function testGivenValidRequestUsingPayPal_requestIsRedirectedToPayPalUrl(): void {
		$client = $this->createClient();
		$client->followRedirects( false );

		$client->request(
			'POST',
			'apply-for-membership',
			$this->newValidHttpParametersUsingPayPal()
		);

		$response = $client->getResponse();
		$this->assertTrue( $response->isRedirect() );
		$this->assertContains( 'sandbox.paypal.com', $response->headers->get( 'Location' ) );
	}

	public function testWhenRedirectingToPayPal_translatedItemNameIsPassed(): void {
		$client = $this->createClient();
		$client->followRedirects( false );

		$client->request(
			'POST',
			'/apply-for-membership',
			$this->newValidHttpParametersUsingPayPal()
		);

		$response = $client->getResponse();
		$this->assertSame( 302, $response->getStatusCode() );
		$this->assertContains( 'item_name=item_name_membership', $response->getContent() );
	}

	public function testCommasInStreetNamesAreRemoved(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {

			$params = $this->newValidHttpParameters();
			$params['strasse'] = 'Nyan, street, ';
			$client->request(
				'POST',
				'apply-for-membership',
				$params
			);

			$application = $factory->getMembershipApplicationRepository()->getApplicationById( 1 );

			$this->assertNotNull( $application );

			$expectedApplication = ValidMembershipApplication::newDomainEntity();
			$expectedApplication->assignId( 1 );
			$expectedApplication->confirm();

			$this->assertEquals( $expectedApplication, $application );
		} );
	}

	public function testWhenCompaniesApply_salutationIsSetToFixedValue(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {

			$params = $this->newValidHttpParametersForCompanies();
			$client->request(
				'POST',
				'apply-for-membership',
				$params
			);

			$application = $factory->getMembershipApplicationRepository()->getApplicationById( 1 );

			$this->assertNotNull( $application );

			$expectedApplication = ValidMembershipApplication::newCompanyApplication();
			$expectedApplication->assignId( 1 );
			$expectedApplication->confirm();

			$this->assertEquals( $expectedApplication, $application );
		} );
	}

	private function newValidHttpParametersForCompanies(): array {
		return [
			'membership_type' => ValidMembershipApplication::MEMBERSHIP_TYPE,

			'adresstyp' => 'firma',
			'firma' => ValidMembershipApplication::APPLICANT_COMPANY_NAME,

			'strasse' => ValidMembershipApplication::APPLICANT_STREET_ADDRESS,
			'postcode' => ValidMembershipApplication::APPLICANT_POSTAL_CODE,
			'ort' => ValidMembershipApplication::APPLICANT_CITY,
			'country' => ValidMembershipApplication::APPLICANT_COUNTRY_CODE,

			'email' => ValidMembershipApplication::APPLICANT_EMAIL_ADDRESS,
			'phone' => ValidMembershipApplication::APPLICANT_PHONE_NUMBER,
			'dob' => ValidMembershipApplication::APPLICANT_DATE_OF_BIRTH,

			'payment_type' => (string)ValidMembershipApplication::PAYMENT_TYPE_DIRECT_DEBIT,
			'membership_fee_interval' => (string)ValidMembershipApplication::PAYMENT_PERIOD_IN_MONTHS,
			'membership_fee' => (string)ValidMembershipApplication::COMPANY_PAYMENT_AMOUNT_IN_EURO, // TODO: change to localized

			'bank_name' => ValidMembershipApplication::PAYMENT_BANK_NAME,
			'iban' => ValidMembershipApplication::PAYMENT_IBAN,
			'bic' => ValidMembershipApplication::PAYMENT_BIC,
			'account_number' => ValidMembershipApplication::PAYMENT_BANK_ACCOUNT,
			'bank_code' => ValidMembershipApplication::PAYMENT_BANK_CODE,
		];
	}

	private function newFixedPaymentDelayCalculator(): PaymentDelayCalculator {
		return new FixedPaymentDelayCalculator(
			new \DateTime( self::FIRST_PAYMENT_DATE )
		);
	}

	public function testCookieFlagsSecureAndHttpOnlyAreSet(): void {
		$client = new Client(
			$this->createSilexApplication(),
			[ 'HTTPS' => true ]
		);

		$client->request(
			'POST',
			'apply-for-membership',
			$this->newValidHttpParameters()
		);

		$cookieJar = $client->getCookieJar();
		$cookieJar->updateFromResponse( $client->getInternalResponse() );
		$cookie = $cookieJar->get( ApplyForMembershipHandler::SUBMISSION_COOKIE_NAME );

		$this->assertTrue( $cookie->isHttpOnly() );
		$this->assertTrue( $cookie->isSecure() );
	}

	public function testGivenDonationReceiptOptOutRequest_applicationHoldsThisValue(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$parameters = $this->newValidHttpParameters();
			$parameters['donationReceipt'] = '0';

			$client->request( Request::METHOD_POST, self::APPLY_FOR_MEMBERSHIP_PATH, $parameters );

			$this->assertFalse( $factory->getMembershipApplicationRepository()->getApplicationById( 1 )->getDonationReceipt() );
		} );
	}
}
