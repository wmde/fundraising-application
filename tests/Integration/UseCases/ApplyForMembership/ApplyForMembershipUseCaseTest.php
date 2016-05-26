<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\ApplyForMembership;

use WMDE\Fundraising\Frontend\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\Domain\Model\EmailAddress;
use WMDE\Fundraising\Frontend\Domain\Model\Iban;
use WMDE\Fundraising\Frontend\Domain\Repositories\MembershipApplicationRepository;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipAppAuthUpdater;
use WMDE\Fundraising\Frontend\Infrastructure\TokenGenerator;
use WMDE\Fundraising\Frontend\Tests\Data\ValidMembershipApplication;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;
use WMDE\Fundraising\Frontend\Tests\Fixtures\InMemoryMembershipApplicationRepository;
use WMDE\Fundraising\Frontend\Tests\Fixtures\TemplateBasedMailerSpy;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\ApplicationValidationResult;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\ApplyForMembershipRequest;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\ApplyForMembershipUseCase;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\MembershipApplicationValidator;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\ApplyForMembershipUseCase
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApplyForMembershipUseCaseTest extends \PHPUnit_Framework_TestCase {

	const ID_OF_NON_EXISTING_APPLICATION = 1337;
	const FIRST_APPLICATION_ID = 1;
	const GENERATED_TOKEN = 'Gimmeh all the access';

	/**
	 * @var MembershipApplicationRepository
	 */
	private $repository;

	/**
	 * @var MembershipAppAuthUpdater|\PHPUnit_Framework_MockObject_MockObject
	 */
	private $authUpdater;

	/**
	 * @var TemplateBasedMailerSpy
	 */
	private $mailer;

	/**
	 * @var TokenGenerator
	 */
	private $tokenGenerator;

	/**
	 * @var MembershipApplicationValidator
	 */
	private $validator;

	public function setUp() {
		$this->repository = new InMemoryMembershipApplicationRepository();
		$this->authUpdater = $this->getMock( MembershipAppAuthUpdater::class );
		$this->mailer = new TemplateBasedMailerSpy( $this );
		$this->tokenGenerator = new FixedTokenGenerator( self::GENERATED_TOKEN );
		$this->validator = $this->newSucceedingValidator();
	}

	private function newSucceedingValidator(): MembershipApplicationValidator {
		$validator = $this->getMockBuilder( MembershipApplicationValidator::class )
			->disableOriginalConstructor()->getMock();

		$validator->expects( $this->any() )
			->method( 'validate' )
			->willReturn( new ApplicationValidationResult() );

		return $validator;
	}

	public function testGivenValidRequest_applicationSucceeds() {
		$response = $this->newUseCase()->applyForMembership( $this->newValidRequest() );

		$this->assertTrue( $response->isSuccessful() );
	}

	private function newUseCase(): ApplyForMembershipUseCase {
		return new ApplyForMembershipUseCase(
			$this->repository,
			$this->authUpdater,
			$this->mailer,
			$this->tokenGenerator,
			$this->validator
		);
	}

	private function newValidRequest(): ApplyForMembershipRequest {
		$request = new ApplyForMembershipRequest();

		$request->setMembershipType( ValidMembershipApplication::MEMBERSHIP_TYPE );
		$request->setApplicantCompanyName( '' );
		$request->setMembershipType( ValidMembershipApplication::MEMBERSHIP_TYPE );
		$request->setApplicantSalutation( ValidMembershipApplication::APPLICANT_SALUTATION );
		$request->setApplicantTitle( ValidMembershipApplication::APPLICANT_TITLE );
		$request->setApplicantFirstName( ValidMembershipApplication::APPLICANT_FIRST_NAME );
		$request->setApplicantLastName( ValidMembershipApplication::APPLICANT_LAST_NAME );
		$request->setApplicantStreetAddress( ValidMembershipApplication::APPLICANT_STREET_ADDRESS );
		$request->setApplicantPostalCode( ValidMembershipApplication::APPLICANT_POSTAL_CODE );
		$request->setApplicantCity( ValidMembershipApplication::APPLICANT_CITY );
		$request->setApplicantCountryCode( ValidMembershipApplication::APPLICANT_COUNTRY_CODE );
		$request->setApplicantEmailAddress( ValidMembershipApplication::APPLICANT_EMAIL_ADDRESS );
		$request->setApplicantPhoneNumber( ValidMembershipApplication::APPLICANT_PHONE_NUMBER );
		$request->setApplicantDateOfBirth( ValidMembershipApplication::APPLICANT_DATE_OF_BIRTH );
		$request->setPaymentIntervalInMonths( ValidMembershipApplication::PAYMENT_PERIOD_IN_MONTHS );
		$request->setPaymentAmountInEuros( (string)ValidMembershipApplication::PAYMENT_AMOUNT_IN_EURO );

		$request->setPaymentBankData( $this->newValidBankData() );

		return $request->assertNoNullFields();
	}

	private function newValidBankData(): BankData {
		$bankData = new BankData();

		$bankData->setIban( new Iban( ValidMembershipApplication::PAYMENT_IBAN ) );
		$bankData->setBic( ValidMembershipApplication::PAYMENT_BIC );
		$bankData->setAccount( ValidMembershipApplication::PAYMENT_BANK_ACCOUNT );
		$bankData->setBankCode( ValidMembershipApplication::PAYMENT_BANK_CODE );
		$bankData->setBankName( ValidMembershipApplication::PAYMENT_BANK_NAME );

		return $bankData->assertNoNullFields()->freeze();
	}

	public function testGivenValidRequest_applicationGetsPersisted() {
		$this->newUseCase()->applyForMembership( $this->newValidRequest() );

		$expectedApplication = ValidMembershipApplication::newDomainEntity();
		$expectedApplication->assignId( self::FIRST_APPLICATION_ID );

		$application = $this->repository->getApplicationById( $expectedApplication->getId() );
		$this->assertNotNull( $application );

		$this->assertEquals( $expectedApplication, $application );
	}

	public function testGivenValidRequest_confirmationEmailIsSend() {
		$this->newUseCase()->applyForMembership( $this->newValidRequest() );

		$this->mailer->assertCalledOnceWith(
			new EmailAddress( ValidMembershipApplication::APPLICANT_EMAIL_ADDRESS ),
			[
				'membershipType' => 'active',
				'membershipFee' => '10.00',
				'paymentIntervalInMonths' => 3,
				'salutation' => 'Herr',
				'title' => '',
				'lastName' => 'The Great'
			]
		);
	}

	public function testGivenValidRequest_tokenIsGeneratedAndAssigned() {
		$this->authUpdater->expects( $this->once() )
			->method( 'allowModificationViaToken' )
			->with(
				$this->equalTo( self::FIRST_APPLICATION_ID ),
				$this->equalTo( self::GENERATED_TOKEN )
			);

		$this->authUpdater->expects( $this->once() )
			->method( 'allowAccessViaToken' )
			->with(
				$this->equalTo( self::FIRST_APPLICATION_ID ),
				$this->equalTo( self::GENERATED_TOKEN )
			);

		$this->newUseCase()->applyForMembership( $this->newValidRequest() );
	}

	public function testGivenValidRequest_tokenIsGeneratedAndReturned() {
		$response = $this->newUseCase()->applyForMembership( $this->newValidRequest() );

		$this->assertSame( self::GENERATED_TOKEN, $response->getAccessToken() );
		$this->assertSame( self::GENERATED_TOKEN, $response->getUpdateToken() );
	}

	public function testWhenValidationFails_failureResultIsReturned() {
		$this->validator = $this->newFailingValidator();

		$response = $this->newUseCase()->applyForMembership( $this->newValidRequest() );

		$this->assertFalse( $response->isSuccessful() );
	}

	private function newFailingValidator(): MembershipApplicationValidator {
		$validator = $this->getMockBuilder( MembershipApplicationValidator::class )
			->disableOriginalConstructor()->getMock();

		$validator->expects( $this->any() )
			->method( 'validate' )
			->willReturn( $this->newInvalidValidationResult() );

		return $validator;
	}

	private function newInvalidValidationResult(): ApplicationValidationResult {
		$invalidResult = $this->getMock( ApplicationValidationResult::class );

		$invalidResult->expects( $this->any() )
			->method( 'isSuccessful' )
			->willReturn( false );

		return $invalidResult;
	}

	public function testGivenValidRequest_moderationIsNotNeeded() {
		$response = $this->newUseCase()->applyForMembership( $this->newValidRequest() );

		$this->assertFalse( $response->getMembershipApplication()->needsModeration() );
	}

	public function testGivenRequestWithHighYearlyAmount_moderationIsNeeded() {
		$request = $this->newValidRequest();
		$request->setPaymentAmountInEuros( '1000.01' );
		$request->setPaymentIntervalInMonths( 12 );

		$this->assertRequestResultsInModeration( $request );
	}

	public function testGivenRequestWithHighQuarterlyAmount_moderationIsNeeded() {
		$request = $this->newValidRequest();
		$request->setPaymentAmountInEuros( '250.01' );
		$request->setPaymentIntervalInMonths( 3 );

		$this->assertRequestResultsInModeration( $request );
	}

	private function assertRequestResultsInModeration( ApplyForMembershipRequest $request ) {
		$response = $this->newUseCase()->applyForMembership( $request );
		$this->assertTrue( $response->getMembershipApplication()->needsModeration() );
	}

}
