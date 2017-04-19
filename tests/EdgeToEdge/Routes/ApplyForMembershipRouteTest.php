<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Silex\Application;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Entities\MembershipApplication;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\MembershipContext\Tests\Data\ValidMembershipApplication;
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

	public function testGivenGetRequestMembership_formIsShown() {
		$this->createAppEnvironment(
			[ ],
			function ( Client $client, FunFunFactory $factory, Application $app ) {

				// @todo Make this the default behaviour of WebRouteTestCase::createAppEnvironment()
				$factory->setTwigEnvironment( $app['twig'] );

				$client->request( 'GET', 'apply-for-membership' );

				$content = $client->getResponse()->getContent();

				$this->assertRegExp(
					'/<form name="memForm" id="memForm" method="POST" action="\/apply-for-membership">/',
					$content
				);

				$this->assertContains(
					'<input type="hidden" name="showMembershipTypeOption" value="true" />',
					$content
				);
			}
		);
	}

	public function testGivenGetRequestSustainingMembership_formIsShown() {
		$this->createAppEnvironment(
			[ ],
			function ( Client $client, FunFunFactory $factory, Application $app ) {

				// @todo Make this the default behaviour of WebRouteTestCase::createAppEnvironment()
				$factory->setTwigEnvironment( $app['twig'] );

				$client->request( 'GET', 'apply-for-membership', [ 'type' => 'sustaining' ] );

				$content = $client->getResponse()->getContent();

				$this->assertRegExp(
					'/<form name="memForm" id="memForm" method="POST" action="\/apply-for-membership">/',
					$content
				);

				$this->assertContains(
					'<input type="hidden" name="showMembershipTypeOption" value="false" />',
					$content
				);
			}
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

			'payment_type' => (string)ValidMembershipApplication::PAYMENT_TYPE_DIRECT_DEBIT,
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
		$this->createAppEnvironment(
			[ ],
			function ( Client $client, FunFunFactory $factory, Application $app ) {

				// @todo Make this the default behaviour of WebRouteTestCase::createAppEnvironment()
				$factory->setTwigEnvironment( $app['twig'] );

				$httpParameters = $this->newValidHttpParameters();
				$httpParameters['membership_fee'] = '1.00'; // TODO: change to localized


				$client->request( 'POST', 'apply-for-membership', $httpParameters );

				preg_match('/data-initial-form-values="(.+?)"/', $client->getResponse()->getContent(), $match);
				$json = html_entity_decode($match[1]);
				$data = json_decode($json, true);

				$this->assertEquals(
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
						'paymentType' => 'BEZ',

					],
					$data
				);
			}
		);
	}

	public function testFlagForShowingMembershipTypeOptionGetsPassedAround() {
		$this->createAppEnvironment(
			[ ],
			function ( Client $client, FunFunFactory $factory, Application $app ) {

				// @todo Make this the default behaviour of WebRouteTestCase::createAppEnvironment()
				$factory->setTwigEnvironment( $app['twig'] );

				$httpParameters = $this->newValidHttpParameters();
				$httpParameters['membership_fee'] = '0';
				$httpParameters['showMembershipTypeOption'] = 'true';

				$client->request(
					'POST',
					'apply-for-membership',
					$httpParameters
				);

				$this->assertContains(
					'<input type="hidden" name="showMembershipTypeOption" value="true" />',
					$client->getResponse()->getContent()
				);
			}
		);
	}

	public function testGivenValidRequest_applicationIsPersisted() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {

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

	public function testGivenValidRequest_confirmationPageContainsCancellationParameters() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setTokenGenerator( new FixedTokenGenerator( self::FIXED_TOKEN ) );

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

	public function testGivenValidRequest_requestIsRedirected() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {

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
			$client->getCookieJar()->set( new Cookie( 'memapp_timestamp', $this->getPastTimestamp() ) );

			$client->request(
				'POST',
				'/apply-for-membership',
				$this->newValidHttpParameters()
			);

			$this->assertContains( 'membership_application_rejected_limit', $client->getResponse()->getContent() );
		} );
	}

	public function testWhenMultipleMembershipInAccordanceToTimeLimit_isNotRejected() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$client->getCookieJar()->set( new Cookie( 'memapp_timestamp', $this->getPastTimestamp( 'PT12M' ) ) );

			$client->request(
				'POST',
				'/apply-for-membership',
				$this->newValidHttpParameters()
			);

			$this->assertNotContains( 'membership_application_rejected_limit', $client->getResponse()->getContent() );
		} );
	}

	private function getPastTimestamp( string $interval = 'PT10S' ) {
		return ( new \DateTime() )->sub( new \DateInterval( $interval ) )->format( 'Y-m-d H:i:s' );
	}

	public function testWhenTrackingCookieExists_valueIsPersisted() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
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

	public function testGivenValidRequestUsingPayPal_applicationIsPersisted() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {

			$client->request(
				'POST',
				'apply-for-membership',
				$this->newValidHttpParametersUsingPayPal()
			);

			$application = $factory->getMembershipApplicationRepository()->getApplicationById( 1 );

			$this->assertNotNull( $application );

			$expectedApplication = ValidMembershipApplication::newDomainEntityUsingPayPal();
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

			'templateCampaign' => ValidMembershipApplication::TEMPLATE_CAMPAIGN,
			'templateName' => ValidMembershipApplication::TEMPLATE_NAME,
		];
	}

	public function testGivenValidRequestUsingPayPal_requestIsRedirectedToPayPalUrl() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {

			$client->followRedirects( false );

			$client->request(
				'POST',
				'apply-for-membership',
				$this->newValidHttpParametersUsingPayPal()
			);

			$response = $client->getResponse();
			$this->assertTrue( $response->isRedirect() );
			$this->assertContains( 'sandbox.paypal.com', $response->headers->get( 'Location' ) );
		} );
	}

	public function testCommasInStreetNamesAreRemoved() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {

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

	public function testWhenCompaniesApply_salutationIsSetToFixedValue() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {

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

}
