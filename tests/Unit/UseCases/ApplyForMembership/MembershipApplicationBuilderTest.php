<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\UseCases\ApplyForMembership;

use WMDE\Fundraising\Frontend\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\Domain\Model\Euro;
use WMDE\Fundraising\Frontend\Domain\Model\Iban;
use WMDE\Fundraising\Frontend\Domain\Model\PersonName;
use WMDE\Fundraising\Frontend\Tests\Data\ValidMembershipApplication;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\ApplyForMembershipRequest;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\MembershipApplicationBuilder;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\MembershipApplicationBuilder
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MembershipApplicationBuilderTest extends \PHPUnit_Framework_TestCase {

	const COMPANY_NAME = 'Malenfant asteroid mining';

	public function testCompanyMembershipRequestGetsBuildCorrectly() {
		$request = $this->newCompanyMembershipRequest();

		$application = ( new MembershipApplicationBuilder() )->newApplicationFromRequest( $request );

		$this->assertEquals(
			$this->getCompanyPersonName(),
			$application->getApplicant()->getPersonName()
		);
	}

	private function newCompanyMembershipRequest() {
		$request = new ApplyForMembershipRequest();

		$request->setMembershipType( ValidMembershipApplication::MEMBERSHIP_TYPE );
		$request->setApplicantType( 'firma' );
		$request->setApplicantCompanyName( self::COMPANY_NAME );
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

	private function getCompanyPersonName(): PersonName {
		$name = PersonName::newCompanyName();

		$name->setCompanyName( self::COMPANY_NAME );
		$name->setSalutation( ValidMembershipApplication::APPLICANT_SALUTATION );
		$name->setTitle( ValidMembershipApplication::APPLICANT_TITLE );
		$name->setFirstName( ValidMembershipApplication::APPLICANT_FIRST_NAME );
		$name->setLastName( ValidMembershipApplication::APPLICANT_LAST_NAME );

		return $name->assertNoNullFields()->freeze();
	}

}
