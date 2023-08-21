<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChange;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidPayments;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\Events\MembershipApplicationCreated;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\BucketLoggerSpy;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedPaymentDelayCalculator;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;
use WMDE\Fundraising\MembershipContext\Domain\Model\Incentive;
use WMDE\Fundraising\MembershipContext\Domain\Model\MembershipApplication;
use WMDE\Fundraising\MembershipContext\Tests\Fixtures\ValidMembershipApplication;
use WMDE\Fundraising\MembershipContext\Tests\TestDoubles\FixedMembershipTokenGenerator;
use WMDE\Fundraising\PaymentContext\Domain\Model\Payment;
use WMDE\Fundraising\PaymentContext\Domain\PaymentDelayCalculator;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Membership\ApplyForMembershipController
 *
 * @requires extension konto_check
 */
class ApplyForMembershipRouteTest extends WebRouteTestCase {

	private const MEMBERSHIP_APPLICATION_ID = 4789;

	private const FIXED_TOKEN = 'fixed_token';
	private const FIRST_PAYMENT_DATE = '2017-09-21';
	private const CORRECT_ACCESS_TOKEN = '4711abc';

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
		$this->modifyEnvironment( function ( FunFunFactory $factory ): void {
			$this->givenStoredDirectDebitDonation( $factory );
		} );
		$client = $this->createClient();
		$httpParameters = [
			'donationId' => 1,
			'donationAccessToken' => self::CORRECT_ACCESS_TOKEN
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
				'bankname' => 'ING-DiBa',
				'paymentType' => 'BEZ',
				'incentives' => [ 0 => 'tote_bag' ]
			],
			$client
		);
	}

	private function givenStoredDirectDebitDonation( FunFunFactory $factory ): void {
		$this->storePayment( $factory, ValidPayments::newDirectDebitPayment() );

		$factory->setDonationTokenGenerator( new FixedTokenGenerator(
			self::CORRECT_ACCESS_TOKEN
		) );

		$factory->getDonationRepository()->storeDonation( ValidDonation::newDirectDebitDonation() );
	}

	private function storePayment( FunFunFactory $factory, Payment $payment ): void {
		$factory->setDonationTokenGenerator( new FixedTokenGenerator(
			self::CORRECT_ACCESS_TOKEN
		) );

		$factory->getPaymentRepository()->storePayment( $payment );
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

			'payment_type' => ValidMembershipApplication::PAYMENT_TYPE_DIRECT_DEBIT->value,
			'membership_fee_interval' => (string)ValidMembershipApplication::PAYMENT_PERIOD_IN_MONTHS->value,
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

			'payment_type' => ValidMembershipApplication::PAYMENT_TYPE_DIRECT_DEBIT->value,
			'membership_fee_interval' => (string)ValidMembershipApplication::PAYMENT_PERIOD_IN_MONTHS->value,
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
		$client = $this->createClient();
		$factory = $this->getFactory();
		$incentive = $this->prepareEnvironment( $factory );

		$parameters = $this->newValidHttpParameters();
		$parameters['incentives'] = [ ValidMembershipApplication::INCENTIVE_NAME ];
		$client->request(
			'POST',
			'apply-for-membership',
			$parameters
		);

		$application = $factory->getMembershipApplicationRepository()->getUnexportedMembershipApplicationById( self::MEMBERSHIP_APPLICATION_ID );

		$this->assertNotNull( $application );

		$expectedApplication = $this->givenConfirmedMembershipApplication( self::MEMBERSHIP_APPLICATION_ID );
		$expectedApplication->addIncentive( $incentive );

		$this->assertEquals( $expectedApplication, $application );
	}

	public function testGivenValidRequest_confirmationPageContainsCancellationParameters(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$this->prepareEnvironment( $factory );

		$client->request(
			'POST',
			'apply-for-membership',
			$this->newValidHttpParameters()
		);

		$responseContent = $client->getResponse()->getContent();

		$this->assertStringContainsString( 'id=' . self::MEMBERSHIP_APPLICATION_ID, $responseContent );
		$this->assertStringContainsString( 'accessToken=' . self::FIXED_TOKEN, $responseContent );
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

	private function givenConfirmedMembershipApplication( int $id ): MembershipApplication {
		$application = ValidMembershipApplication::newDomainEntity( $id );
		$application->confirm();
		return $application;
	}

	public function testCommasInStreetNamesAreRemoved(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$this->prepareEnvironment( $factory );
		$params = $this->newValidHttpParameters();
		$params['strasse'] = 'Nyan, street, ';
		$client->request(
			'POST',
			'apply-for-membership',
			$params
		);

		$application = $factory->getMembershipApplicationRepository()->getUnexportedMembershipApplicationById( self::MEMBERSHIP_APPLICATION_ID );

		$this->assertNotNull( $application );

		$expectedApplication = $this->givenConfirmedMembershipApplication( self::MEMBERSHIP_APPLICATION_ID );
		$this->assertEquals( $expectedApplication, $application );
	}

	public function testWhenCompaniesApply_salutationIsSetToFixedValue(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$this->prepareEnvironment( $factory );
		$params = $this->newValidHttpParametersForCompanies();
		$client->request(
			'POST',
			'apply-for-membership',
			$params
		);

		$application = $factory->getMembershipApplicationRepository()->getUnexportedMembershipApplicationById( self::MEMBERSHIP_APPLICATION_ID );

		$this->assertNotNull( $application );

		$expectedApplication = ValidMembershipApplication::newCompanyApplication( self::MEMBERSHIP_APPLICATION_ID );
		$expectedApplication->confirm();

		$this->assertEquals( $expectedApplication, $application );
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

			'payment_type' => ValidMembershipApplication::PAYMENT_TYPE_DIRECT_DEBIT->value,
			'membership_fee_interval' => (string)ValidMembershipApplication::PAYMENT_PERIOD_IN_MONTHS->value,
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
		$client = $this->createClient();
		$factory = $this->getFactory();
		$parameters = $this->newValidHttpParameters();
		$parameters['donationReceipt'] = '0';

		$client->request( Request::METHOD_POST, self::APPLY_FOR_MEMBERSHIP_PATH, $parameters );

		$this->assertFalse( $factory->getMembershipApplicationRepository()->getUnexportedMembershipApplicationById( 1 )->getDonationReceipt() );
	}

	public function testGivenValidRequest_bucketsAreLogged(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
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
	}

	public function testGivenInvalidRequest_errorsAreLogged(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
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

	public function testGivenValidRequest_AddressChangeRecordIsCreated(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$this->prepareEnvironment( $factory );

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
	}

	private function prepareEnvironment( FunFunFactory $factory ): Incentive {
		$factory->setMembershipTokenGenerator( new FixedMembershipTokenGenerator( self::FIXED_TOKEN ) );
		$factory->setPaymentDelayCalculator( $this->newFixedPaymentDelayCalculator() );
		$incentive = new Incentive( ValidMembershipApplication::INCENTIVE_NAME );
		$this->insertIncentives( $factory, $incentive );
		$factory->getConnection()->executeStatement( 'UPDATE last_generated_membership_id SET membership_id = ' . ( self::MEMBERSHIP_APPLICATION_ID - 1 ) );

		return $incentive;
	}

	private function insertIncentives( FunFunFactory $factory, Incentive ...$incentives ): void {
		$em = $factory->getEntityManager();
		foreach ( $incentives as $incentive ) {
			$em->persist( $incentive );
		}
		$em->flush();
	}
}
