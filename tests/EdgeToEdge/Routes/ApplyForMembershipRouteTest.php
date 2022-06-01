<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Symfony\Component\BrowserKit\AbstractBrowser as Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChange;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\Events\MembershipApplicationCreated;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\Translation\TranslatorInterface;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\BucketLoggerSpy;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedPaymentDelayCalculator;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;
use WMDE\Fundraising\MembershipContext\DataAccess\DoctrineEntities\MembershipApplication;
use WMDE\Fundraising\MembershipContext\Domain\Model\Incentive;
use WMDE\Fundraising\MembershipContext\Tests\Data\ValidMembershipApplication;
use WMDE\Fundraising\MembershipContext\Tests\Fixtures\FixedMembershipTokenGenerator;
use WMDE\Fundraising\PaymentContext\Domain\Model\PayPalData;
use WMDE\Fundraising\PaymentContext\Domain\PaymentDelayCalculator;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Membership\ApplyForMembershipController
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

		$crawler = $client->request( 'GET', 'apply-for-membership', [ 'type' => 'sustaining' ] );

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
		$this->modifyEnvironment( static function ( FunFunFactory $factory ): void {
			$factory->setDonationTokenGenerator( new FixedTokenGenerator( '4711abc' ) );
			$factory->getDonationRepository()
				->storeDonation( ValidDonation::newDirectDebitDonation() );
		} );
		$client = $this->createClient();
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
				'street' => 'Nyan Street',
				'postcode' => '12345',
				'city' => 'Berlin',
				'country' => 'DE',
				'email' => 'foo@bar.baz',
				'iban' => 'DE12500105170648489890',
				'bic' => 'INGDDEFFXXX',
				'accountNumber' => '0648489890',
				'bankCode' => '50010517',
				'bankname' => 'ING-DiBa',
				'paymentType' => 'BEZ',
				'incentives' => [ 0 => 'tote_bag' ]
			],
			$client
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
			'membership_fee' => '1000',

			'bank_name' => ValidMembershipApplication::PAYMENT_BANK_NAME,
			'iban' => ValidMembershipApplication::PAYMENT_IBAN,
			'bic' => ValidMembershipApplication::PAYMENT_BIC,
			'account_number' => ValidMembershipApplication::PAYMENT_BANK_ACCOUNT,
			'bank_code' => ValidMembershipApplication::PAYMENT_BANK_CODE,
		];
	}

	private function newInvalidValidHttpParameters(): array {
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

			'email' => 'DEFINITELY_NOT_AN_EMAIL',
			'phone' => ValidMembershipApplication::APPLICANT_PHONE_NUMBER,
			'dob' => 'BLAHBLAH',

			'payment_type' => (string)ValidMembershipApplication::PAYMENT_TYPE_DIRECT_DEBIT,
			'membership_fee_interval' => (string)ValidMembershipApplication::PAYMENT_PERIOD_IN_MONTHS,
			'membership_fee' => '1000',

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
		$httpParameters['membership_fee'] = '100';

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
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$factory->setPaymentDelayCalculator( $this->newFixedPaymentDelayCalculator() );
			$incentive = new Incentive( ValidMembershipApplication::INCENTIVE_NAME );
			$this->insertIncentives( $factory, $incentive );

			$parameters = $this->newValidHttpParameters();
			$parameters['incentives'] = [ ValidMembershipApplication::INCENTIVE_NAME ];
			$client->request(
				'POST',
				'apply-for-membership',
				$parameters
			);

			$application = $factory->getMembershipApplicationRepository()->getApplicationById( 1 );

			$this->assertNotNull( $application );

			$expectedApplication = ValidMembershipApplication::newDomainEntity();
			$expectedApplication->assignId( 1 );
			$expectedApplication->addIncentive( $incentive );

			$this->assertEquals( $expectedApplication, $application );
		} );
	}

	public function testGivenValidRequestWithTracking_trackingIsPersisted(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$factory->setPaymentDelayCalculator( $this->newFixedPaymentDelayCalculator() );
			$incentive = new Incentive( ValidMembershipApplication::INCENTIVE_NAME );
			$this->insertIncentives( $factory, $incentive );

			$parameters = $this->newValidHttpParameters();
			$parameters['piwik_campaign'] = 'test';
			$parameters['piwik_kwd'] = 'blue';

			$client->request(
				'POST',
				'apply-for-membership',
				$parameters
			);

			$application = $this->getApplicationFromDatabase( $factory );
			$this->assertSame( 'test/blue', $application->getTracking() );
		} );
	}

	public function testGivenValidRequest_confirmationPageContainsCancellationParameters(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$factory->setMembershipTokenGenerator( new FixedMembershipTokenGenerator( self::FIXED_TOKEN ) );

			$client->request(
				'POST',
				'apply-for-membership',
				$this->newValidHttpParameters()
			);

				$responseContent = $client->getResponse()->getContent();

				$this->assertStringContainsString( 'id=1', $responseContent );
				$this->assertStringContainsString( 'accessToken=' . self::FIXED_TOKEN, $responseContent );
		}
		);
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
		$this->assertStringContainsString( 'show-membership-confirmation', $response->headers->get( 'Location' ) );
	}

	public function testWhenApplicationGetsPersisted_timestampIsStoredInSession(): void {
		$client = $this->createClient();
		$client->request(
			'POST',
			'/apply-for-membership',
			$this->newValidHttpParameters()
		);

		/** @var SessionInterface $session */
		$session = $client->getRequest()->getSession();
		$lastMembership = $session->get( FunFunFactory::MEMBERSHIP_RATE_LIMIT_SESSION_KEY );
		$this->assertNotNull( $lastMembership );
		$this->assertEqualsWithDelta( time(), $lastMembership->getTimestamp(), 5.0, 'Timestamp should be not more than 5 seconds old' );
	}

	public function testWhenMultipleMembershipFormSubmissions_requestGetsRejected(): void {
		$client = $this->createClient();
		$this->prepareSessionValues( [ FunFunFactory::MEMBERSHIP_RATE_LIMIT_SESSION_KEY => new \DateTimeImmutable() ] );

		$client->request(
			'POST',
			'/apply-for-membership',
			$this->newValidHttpParameters()
		);

		$this->assertStringContainsString( 'membership_application_rejected_limit', $client->getResponse()->getContent() );
	}

	public function testWhenMultipleMembershipInAccordanceToTimeLimit_isNotRejected(): void {
		$client = $this->createClient();
		$someMinutesAgo = ( new \DateTimeImmutable() )->sub( new \DateInterval( 'PT12M' ) );
		$this->prepareSessionValues( [ FunFunFactory::MEMBERSHIP_RATE_LIMIT_SESSION_KEY => $someMinutesAgo ] );

		$client->request(
			'POST',
			'/apply-for-membership',
			$this->newValidHttpParameters()
		);

		$this->assertStringNotContainsString( 'membership_application_rejected_limit', $client->getResponse()->getContent() );
	}

	private function getApplicationFromDatabase( FunFunFactory $factory ): MembershipApplication {
		$repository = $factory->getEntityManager()->getRepository( MembershipApplication::class );
		$application = $repository->find( 1 );
		$this->assertInstanceOf( MembershipApplication::class, $application );
		return $application;
	}

	public function testGivenValidRequestUsingPayPal_applicationIsPersisted(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$factory->setPaymentDelayCalculator( $this->newFixedPaymentDelayCalculator() );

			$client->request(
				'POST',
				'/apply-for-membership',
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
			'membership_fee' => '1000',
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
		$this->assertStringContainsString( 'sandbox.paypal.com', $response->headers->get( 'Location' ) );
	}

	public function testWhenRedirectingToPayPal_translatedItemNameIsPassed(): void {
		$this->modifyEnvironment( function ( FunFunFactory $factory ) {
			$translator = $this->createMock( TranslatorInterface::class );
			$translator->expects( $this->once() )
				->method( 'trans' )
				->with( 'paypal_item_name_membership' )
				->willReturn( 'Ihre Mitgliedschaft bei Wikimedia' );
			$factory->setPaymentProviderItemsTranslator( $translator );
		} );
		$client = $this->createClient();
		$client->followRedirects( false );

		$client->request(
			'POST',
			'/apply-for-membership',
			$this->newValidHttpParametersUsingPayPal()
		);

		$response = $client->getResponse();
		$this->assertSame( 302, $response->getStatusCode() );
		$this->assertStringContainsString( 'item_name=Ihre+Mitgliedschaft+bei+Wikimedia', $response->getContent() );
	}

	public function testCommasInStreetNamesAreRemoved(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
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

			$this->assertEquals( $expectedApplication, $application );
		} );
	}

	public function testWhenCompaniesApply_salutationIsSetToFixedValue(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
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
			'membership_fee' => '2500',

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

	public function testGivenDonationReceiptOptOutRequest_applicationHoldsThisValue(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$parameters = $this->newValidHttpParameters();
			$parameters['donationReceipt'] = '0';

			$client->request( Request::METHOD_POST, self::APPLY_FOR_MEMBERSHIP_PATH, $parameters );

			$this->assertFalse( $factory->getMembershipApplicationRepository()->getApplicationById( 1 )->getDonationReceipt() );
		} );
	}

	public function testGivenValidRequest_andCookieConsentGiven_bucketsAreLogged(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$factory->setPaymentDelayCalculator( $this->newFixedPaymentDelayCalculator() );
			$bucketLogger = new BucketLoggerSpy();
			$factory->setBucketLogger( $bucketLogger );

			$client->request(
				'POST',
				'apply-for-membership',
				$this->newValidHttpParameters()
			);

			$this->assertSame( 1, $bucketLogger->getEventCount() );
			$this->assertInstanceOf( MembershipApplicationCreated::class, $bucketLogger->getFirstEvent() );
		} );
	}

	public function testGivenInvalidRequest_errorsAreLogged(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
				$testHandler = new TestHandler();
				$factory->setLogger( new Logger( 'TestLogger', [ $testHandler ] ) );
				$client->request(
					'POST',
					'apply-for-membership',
					$this->newInvalidValidHttpParameters()
				);
				$this->assertTrue( $testHandler->hasWarningRecords() );
				foreach ( $testHandler->getRecords() as $record ) {
					$this->assertEquals( 'Unexpected server-side form validation errors.', $record['message'] );
				}
		}
		);
	}

	public function testGivenValidRequest_AddressChangeRecordIsCreated(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$factory->setPaymentDelayCalculator( $this->newFixedPaymentDelayCalculator() );

			$client->request(
				'POST',
				'apply-for-membership',
				$this->newValidHttpParameters()
			);

			/** @var AddressChange[] $addressChanges */
			$addressChanges = $factory->getEntityManager()->getRepository( AddressChange::class )->findAll();
			$this->assertCount( 1, $addressChanges );
			$this->assertTrue( $addressChanges[0]->getExternalIdType() === AddressChange::EXTERNAL_ID_TYPE_MEMBERSHIP );
			$this->assertTrue( $addressChanges[0]->isPersonalAddress() );
		} );
	}

	private function insertIncentives( FunFunFactory $factory, Incentive ...$incentives ): void {
		$em = $factory->getEntityManager();
		foreach ( $incentives as $incentive ) {
			$em->persist( $incentive );
		}
		$em->flush();
	}
}
