<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\CancelMembershipApplication;

use WMDE\Fundraising\Frontend\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\Domain\Model\Euro;
use WMDE\Fundraising\Frontend\Domain\Model\Iban;
use WMDE\Fundraising\Frontend\Domain\Repositories\MembershipApplicationRepository;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipAppAuthUpdater;
use WMDE\Fundraising\Frontend\Tests\Data\ValidMembershipApplication;
use WMDE\Fundraising\Frontend\Tests\Fixtures\InMemoryMembershipApplicationRepository;
use WMDE\Fundraising\Frontend\Tests\Fixtures\TemplateBasedMailerSpy;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\ApplyForMembershipRequest;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\ApplyForMembershipUseCase;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\ApplyForMembershipUseCase
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApplyForMembershipUseCaseTest extends \PHPUnit_Framework_TestCase {

	const ID_OF_NON_EXISTING_APPLICATION = 1337;

	/**
	 * @var MembershipApplicationRepository
	 */
	private $repository;

	/**
	 * @var MembershipAppAuthUpdater
	 */
	private $authUpdater;

	/**
	 * @var TemplateBasedMailerSpy
	 */
	private $mailer;

	public function setUp() {
		$this->repository = new InMemoryMembershipApplicationRepository();
		$this->authUpdater = $this->getMock( MembershipAppAuthUpdater::class );
		$this->mailer = new TemplateBasedMailerSpy( $this );
	}

	public function testGivenValidRequest_applicationSucceeds() {
		$response = $this->newUseCase()->applyForMembership( $this->newValidRequest() );

		$this->assertTrue( $response->isSuccessful() );
	}

	private function newValidRequest() {
		$request = new ApplyForMembershipRequest();

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
		$request->setPaymentAmount( Euro::newFromInt( ValidMembershipApplication::PAYMENT_AMOUNT_IN_EURO ) );

		$request->setPaymentBankData( $this->newValidBankData() );

		return $request->assertNoNullFields()->freeze();
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

	private function newUseCase(): ApplyForMembershipUseCase {
		return new ApplyForMembershipUseCase(
			$this->repository,
			$this->authUpdater,
			$this->mailer
		);
	}

	public function testGivenValidRequest_applicationGetsPersisted() {
		$this->newUseCase()->applyForMembership( $this->newValidRequest() );

		$expectedApplication = ValidMembershipApplication::newDomainEntity();
		$expectedApplication->assignId( 1 );

		$application = $this->repository->getApplicationById( $expectedApplication->getId() );
		$this->assertNotNull( $application );

		$this->assertEquals( $expectedApplication, $application );
	}

}
