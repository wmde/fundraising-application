<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Entities\MembershipApplication;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\Data\ValidMembershipApplication;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ApplyForMembershipRouteTest extends WebRouteTestCase {

	const FIXED_TOKEN = 'fixed_token';
	const FIXED_TIMESTAMP = '2020-12-01 20:12:01';

	public function setUp() {
		if ( !function_exists( 'lut_init' ) ) {
			$this->markTestSkipped( 'The konto_check needs to be installed!' );
		}
		parent::setUp();
	}

	public function testGivenGetRequest_resultHasMethodNotAllowedStatus() {
		$this->assertGetRequestCausesMethodNotAllowedResponse(
			'apply-for-membership',
			[]
		);
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

			'membership_fee_interval' => (string)ValidMembershipApplication::PAYMENT_PERIOD_IN_MONTHS,
			'membership_fee' => (string)ValidMembershipApplication::PAYMENT_AMOUNT_IN_EURO, // TODO: change to localized

			'bank_name' => ValidMembershipApplication::PAYMENT_BANK_NAME,
			'iban' => ValidMembershipApplication::PAYMENT_IBAN,
			'bic' => ValidMembershipApplication::PAYMENT_BIC,
			'account_number' => ValidMembershipApplication::PAYMENT_BANK_ACCOUNT,
			'bank_code' => ValidMembershipApplication::PAYMENT_BANK_CODE,

			'templateCampaign' => ValidMembershipApplication::TEMPLATE_CAMPAIGN,
			'templateName' => ValidMembershipApplication::TEMPLATE_NAME,
		];
	}

	public function testGivenRequestWithInsufficientAmount_failureResponseIsReturned() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();

			$httpParameters = $this->newValidHttpParameters();
			$httpParameters['membership_fee'] = '1.00'; // TODO: change to localized

			$client->request(
				'POST',
				'apply-for-membership',
				$httpParameters
			);

			$this->assertFormFieldsArePopulated( $client->getResponse()->getContent() );
		} );
	}

	private function assertFormFieldsArePopulated( $response ) {
		$this->assertContains( 'initialFormValues.addressType: person', $response );
		$this->assertContains( 'initialFormValues.salutation: Herr', $response );
		$this->assertContains( 'initialFormValues.title: ', $response );
		$this->assertContains( 'initialFormValues.firstName: Potato', $response );
		$this->assertContains( 'initialFormValues.lastName: The Great', $response );
		$this->assertContains( 'initialFormValues.companyName: ', $response );
		$this->assertContains( 'initialFormValues.street: Nyan street', $response );
		$this->assertContains( 'initialFormValues.postcode: 1234', $response );
		$this->assertContains( 'initialFormValues.city: Berlin', $response );
		$this->assertContains( 'initialFormValues.country: DE', $response );
		$this->assertContains( 'initialFormValues.email: jeroendedauw@gmail.com', $response );
		$this->assertContains( 'initialFormValues.iban: DE12500105170648489890', $response );
		$this->assertContains( 'initialFormValues.bic: INGDDEFFXXX', $response );
		$this->assertContains( 'initialFormValues.accountNumber: 0648489890', $response );
		$this->assertContains( 'initialFormValues.bankCode: 50010517', $response );
		$this->assertContains( 'initialFormValues.bankname: ING-DiBa', $response );
	}

	public function testFlagForShowingMembershipTypeOptionGetsPassedAround() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();

			$httpParameters = $this->newValidHttpParameters();
			$httpParameters['membership_fee'] = '0';
			$httpParameters['showMembershipTypeOption'] = 'true';

			$client->request(
				'POST',
				'apply-for-membership',
				$httpParameters
			);

			$this->assertContains( 'showMembershipTypeOption: true', $client->getResponse()->getContent() );
		} );
	}

	public function testGivenValidRequest_applicationIsPersisted() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();

			$client->request(
				'POST',
				'apply-for-membership',
				$this->newValidHttpParameters()
			);

			$application = $factory->getMembershipApplicationRepository()->getApplicationById( 1 );

			$this->assertNotNull( $application );

			$expectedApplication = ValidMembershipApplication::newDomainEntity();
			$expectedApplication->assignId( 1 );

			$this->assertEquals( $expectedApplication, $application );
		} );
	}

	public function testGivenValidRequest_confirmationPageContainsCancellationParameters() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();
			$factory->setTokenGenerator( new FixedTokenGenerator( self::FIXED_TOKEN ) );

			$client->request(
				'POST',
				'apply-for-membership',
				$this->newValidHttpParameters()
			);

			$responseContent = $client->getResponse()->getContent();

			$this->assertContains( 'id=1', $responseContent );
			$this->assertContains( 'token=' . self::FIXED_TOKEN, $responseContent );
		} );
	}

	public function testGivenValidRequest_requestIsRedirected() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();

			$client->followRedirects( false );

			$client->request(
				'POST',
				'apply-for-membership',
				$this->newValidHttpParameters()
			);

			$response = $client->getResponse();
			$this->assertTrue( $response->isRedirect() );
			$this->assertContains( 'show-membership-confirmation', $response->headers->get( 'Location' ) );
		} );
	}

	public function testWhenApplicationGetsPersisted_timestampIsStoredInCookie() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();

			$client->request(
				'POST',
				'/apply-for-membership',
				$this->newValidHttpParameters()
			);

			$cookie = $client->getCookieJar()->get( 'memapp_timestamp' );
			$this->assertNotNull( $cookie );
			$donationTimestamp = new \DateTime( $cookie->getValue() );
			$this->assertEquals( time(), $donationTimestamp->getTimestamp(), 'Timestamp should be not more than 5 seconds old', 5.0 );
		} );
	}

	public function testWhenMultipleMembershipFormSubmissions_requestGetsRejected() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();
			$client->getCookieJar()->set( new Cookie( 'memapp_timestamp', $this->getPastTimestamp() ) );

			$client->request(
				'POST',
				'/apply-for-membership',
				$this->newValidHttpParameters()
			);

			$this->assertContains( 'Sie haben vor sehr kurzer Zeit bereits', $client->getResponse()->getContent() );
		} );
	}

	public function testWhenMultipleMembershipInAccordanceToTimeLimit_isNotRejected() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();
			$client->getCookieJar()->set( new Cookie( 'memapp_timestamp', $this->getPastTimestamp( 'PT12M' ) ) );

			$client->request(
				'POST',
				'/apply-for-membership',
				$this->newValidHttpParameters()
			);

			$this->assertNotContains( 'Sie haben vor sehr kurzer Zeit bereits', $client->getResponse()->getContent() );
		} );
	}

	private function getPastTimestamp( string $interval = 'PT10S' ) {
		return ( new \DateTime() )->sub( new \DateInterval( $interval ) )->format( 'Y-m-d H:i:s' );
	}

	public function testWhenTrackingCookieExists_valueIsPersisted() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();
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

}
