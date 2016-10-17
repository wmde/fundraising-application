<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Data;

use WMDE\Fundraising\Frontend\MembershipContext\Tracking\MembershipApplicationTrackingInfo;
use WMDE\Fundraising\Frontend\MembershipContext\UseCases\ApplyForMembership\ApplyForMembershipRequest;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\Iban;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ValidMembershipApplicationRequest {

	/**
	 * Returns a request with the same data as the constants in @see ValidMembershipApplication
	 *
	 * The object is purposely left mutable so tests can change a single value to something invalid.
	 */
	public static function newValidRequest(): ApplyForMembershipRequest {
		return ( new self() )->createValidRequest();
	}

	private function createValidRequest(): ApplyForMembershipRequest {
		$request = new ApplyForMembershipRequest();

		$request->setMembershipType( ValidMembershipApplication::MEMBERSHIP_TYPE );

		$request->setApplicantFirstName( ValidMembershipApplication::APPLICANT_FIRST_NAME );
		$request->setApplicantLastName( ValidMembershipApplication::APPLICANT_LAST_NAME );
		$request->setApplicantSalutation( ValidMembershipApplication::APPLICANT_SALUTATION );
		$request->setApplicantTitle( ValidMembershipApplication::APPLICANT_TITLE );
		$request->setApplicantCompanyName( '' );

		$request->setApplicantDateOfBirth( ValidMembershipApplication::APPLICANT_DATE_OF_BIRTH );

		$request->setApplicantCity( ValidMembershipApplication::APPLICANT_CITY );
		$request->setApplicantCountryCode( ValidMembershipApplication::APPLICANT_COUNTRY_CODE );
		$request->setApplicantPostalCode( ValidMembershipApplication::APPLICANT_POSTAL_CODE );
		$request->setApplicantStreetAddress( ValidMembershipApplication::APPLICANT_STREET_ADDRESS );

		$request->setApplicantEmailAddress( ValidMembershipApplication::APPLICANT_EMAIL_ADDRESS );
		$request->setApplicantPhoneNumber( ValidMembershipApplication::APPLICANT_PHONE_NUMBER );

		$request->setMembershipType( ValidMembershipApplication::MEMBERSHIP_TYPE );
		$request->setPaymentType( ValidMembershipApplication::PAYMENT_TYPE_DIRECT_DEBIT );
		$request->setPaymentIntervalInMonths( ValidMembershipApplication::PAYMENT_PERIOD_IN_MONTHS );
		$request->setPaymentAmountInEuros( (string)ValidMembershipApplication::PAYMENT_AMOUNT_IN_EURO );

		$request->setTrackingInfo( $this->newTrackingInfo() );
		$request->setPiwikTrackingString( 'foo/bar' );

		$request->setBankData( $this->newValidBankData() );

		return $request->assertNoNullFields();
	}

	private function newValidBankData(): BankData {
		$bankData = new BankData();

		$bankData->setAccount( ValidMembershipApplication::PAYMENT_BANK_ACCOUNT );
		$bankData->setBankCode( ValidMembershipApplication::PAYMENT_BANK_CODE );
		$bankData->setBankName( ValidMembershipApplication::PAYMENT_BANK_NAME );
		$bankData->setBic( ValidMembershipApplication::PAYMENT_BIC );
		$bankData->setIban( new Iban( ValidMembershipApplication::PAYMENT_IBAN ) );

		return $bankData->assertNoNullFields();
	}

	private function newTrackingInfo() {
		return new MembershipApplicationTrackingInfo(
			ValidMembershipApplication::TEMPLATE_CAMPAIGN,
			ValidMembershipApplication::TEMPLATE_NAME
		);
	}

}
