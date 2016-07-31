<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\UseCases\ApplyForMembership;

use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\Domain\Model\PersonName;
use WMDE\Fundraising\Frontend\Domain\Model\PhysicalAddress;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Tracking\MembershipApplicationTrackingInfo;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\UseCases\ApplyForMembership\ApplyForMembershipRequest;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\UseCases\ApplyForMembership\MembershipApplicationBuilder;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\Iban;
use WMDE\Fundraising\Frontend\Tests\Data\ValidMembershipApplication;

/**
 * @covers WMDE\Fundraising\Frontend\MembershipApplicationContext\UseCases\ApplyForMembership\MembershipApplicationBuilder
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MembershipApplicationBuilderTest extends \PHPUnit_Framework_TestCase {

	const COMPANY_NAME = 'Malenfant asteroid mining';
	const OMIT_OPTIONAL_FIELDS = true;

	public function testCompanyMembershipRequestGetsBuildCorrectly() {
		$request = $this->newCompanyMembershipRequest();

		$application = ( new MembershipApplicationBuilder() )->newApplicationFromRequest( $request );

		$this->assertIsExpectedCompanyPersonName( $application->getApplicant()->getPersonName() );
		$this->assertIsExpectedAddress( $application->getApplicant()->getPhysicalAddress() );

		$this->assertEquals(
			Euro::newFromInt( ValidMembershipApplication::PAYMENT_AMOUNT_IN_EURO ),
			$application->getPayment()->getAmount()
		);
	}

	private function newCompanyMembershipRequest( bool $omitOptionalFields = false ) {
		$request = new ApplyForMembershipRequest();

		$request->setMembershipType( ValidMembershipApplication::MEMBERSHIP_TYPE );
		$request->markApplicantAsCompany();
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
		$request->setPaymentIntervalInMonths( ValidMembershipApplication::PAYMENT_PERIOD_IN_MONTHS );
		$request->setPaymentAmountInEuros( (string)ValidMembershipApplication::PAYMENT_AMOUNT_IN_EURO );
		$request->setPaymentBankData( $this->newValidBankData() );
		$request->setApplicantPhoneNumber(
			$omitOptionalFields ? '' : ValidMembershipApplication::APPLICANT_PHONE_NUMBER
		);
		$request->setApplicantDateOfBirth(
			$omitOptionalFields ? '' : ValidMembershipApplication::APPLICANT_DATE_OF_BIRTH
		);
		$request->setTrackingInfo( $this->newTrackingInfo() );
		$request->setPiwikTrackingString( 'foo/bar' );

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

	private function newTrackingInfo(): MembershipApplicationTrackingInfo {
		return new MembershipApplicationTrackingInfo(
			ValidMembershipApplication::TEMPLATE_CAMPAIGN,
			ValidMembershipApplication::TEMPLATE_NAME
		);
	}

	private function assertIsExpectedCompanyPersonName( PersonName $name ) {
		$this->assertEquals(
			$this->getCompanyPersonName(),
			$name
		);
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

	private function assertIsExpectedAddress( PhysicalAddress $address ) {
		$this->assertEquals(
			$this->getPhysicalAddress(),
			$address
		);
	}

	private function getPhysicalAddress(): PhysicalAddress {
		$address = new PhysicalAddress();

		$address->setStreetAddress( ValidMembershipApplication::APPLICANT_STREET_ADDRESS );
		$address->setPostalCode( ValidMembershipApplication::APPLICANT_POSTAL_CODE );
		$address->setCity( ValidMembershipApplication::APPLICANT_CITY );
		$address->setCountryCode( ValidMembershipApplication::APPLICANT_COUNTRY_CODE );

		return $address->assertNoNullFields()->freeze();
	}

	public function testWhenNoBirthDateAndPhoneNumberIsGiven_membershipApplicationIsStillBuiltCorrectly() {
		$request = $this->newCompanyMembershipRequest( self::OMIT_OPTIONAL_FIELDS );

		$application = ( new MembershipApplicationBuilder() )->newApplicationFromRequest( $request );

		$this->assertIsExpectedCompanyPersonName( $application->getApplicant()->getPersonName() );
		$this->assertIsExpectedAddress( $application->getApplicant()->getPhysicalAddress() );

		$this->assertEquals(
			Euro::newFromInt( ValidMembershipApplication::PAYMENT_AMOUNT_IN_EURO ),
			$application->getPayment()->getAmount()
		);
	}

}
