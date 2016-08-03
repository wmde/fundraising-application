<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Data;

use WMDE\Euro\Euro;
use WMDE\Fundraising\Entities\MembershipApplication as DoctrineMembershipApplication;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Model\ApplicantAddress;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Model\ApplicantName;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Model\EmailAddress;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Model\Applicant;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Model\Application;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Model\Payment;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Model\PhoneNumber;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\Iban;

/**
 * newDomainEntity and newDoctrineEntity return equivalent objects.
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ValidMembershipApplication {

	const APPLICANT_FIRST_NAME = 'Potato';
	const APPLICANT_LAST_NAME = 'The Great';
	const APPLICANT_SALUTATION = 'Herr';
	const APPLICANT_TITLE = '';

	const APPLICANT_DATE_OF_BIRTH = '1990-01-01';

	const APPLICANT_CITY = 'Berlin';
	const APPLICANT_COUNTRY_CODE = 'DE';
	const APPLICANT_POSTAL_CODE = '1234';
	const APPLICANT_STREET_ADDRESS = 'Nyan street';

	const APPLICANT_EMAIL_ADDRESS = 'jeroendedauw@gmail.com';
	const APPLICANT_PHONE_NUMBER = '1337-1337-1337';

	const MEMBERSHIP_TYPE = Application::ACTIVE_MEMBERSHIP;
	const PAYMENT_PERIOD_IN_MONTHS = 3;
	const PAYMENT_AMOUNT_IN_EURO = 10;

	const PAYMENT_BANK_ACCOUNT = '0648489890';
	const PAYMENT_BANK_CODE = '50010517';
	const PAYMENT_BANK_NAME = 'ING-DiBa';
	const PAYMENT_BIC = 'INGDDEFFXXX';
	const PAYMENT_IBAN = 'DE12500105170648489890';

	const TEMPLATE_CAMPAIGN = 'test161012';
	const TEMPLATE_NAME = 'Some_Membership_Form_Template.twig';

	public static function newDomainEntity(): Application {
		return ( new self() )->createApplication();
	}

	private function createApplication(): Application {
		return Application::newApplication(
			self::MEMBERSHIP_TYPE,
			new Applicant(
				$this->newApplicantName(),
				$this->newAddress(),
				new EmailAddress( self::APPLICANT_EMAIL_ADDRESS ),
				new PhoneNumber( self::APPLICANT_PHONE_NUMBER ),
				new \DateTime( self::APPLICANT_DATE_OF_BIRTH )
			),
			new Payment(
				self::PAYMENT_PERIOD_IN_MONTHS,
				Euro::newFromFloat( self::PAYMENT_AMOUNT_IN_EURO ),
				$this->newBankData()
			)
		);
	}

	private function newApplicantName(): ApplicantName {
		$personName = ApplicantName::newPrivatePersonName();

		$personName->setFirstName( self::APPLICANT_FIRST_NAME );
		$personName->setLastName( self::APPLICANT_LAST_NAME );
		$personName->setSalutation( self::APPLICANT_SALUTATION );
		$personName->setTitle( self::APPLICANT_TITLE );

		return $personName->freeze()->assertNoNullFields();
	}

	private function newAddress(): ApplicantAddress {
		$address = new ApplicantAddress();

		$address->setCity( self::APPLICANT_CITY );
		$address->setCountryCode( self::APPLICANT_COUNTRY_CODE );
		$address->setPostalCode( self::APPLICANT_POSTAL_CODE );
		$address->setStreetAddress( self::APPLICANT_STREET_ADDRESS );

		return $address->freeze()->assertNoNullFields();
	}

	private function newBankData(): BankData {
		$bankData = new BankData();

		$bankData->setAccount( self::PAYMENT_BANK_ACCOUNT );
		$bankData->setBankCode( self::PAYMENT_BANK_CODE );
		$bankData->setBankName( self::PAYMENT_BANK_NAME );
		$bankData->setBic( self::PAYMENT_BIC );
		$bankData->setIban( new Iban( self::PAYMENT_IBAN ) );

		return $bankData->freeze()->assertNoNullFields();
	}

	public static function newDoctrineEntity(): DoctrineMembershipApplication {
		return ( new self() )->createDoctrineApplication();
	}

	private function createDoctrineApplication(): DoctrineMembershipApplication {
		$application = new DoctrineMembershipApplication();

		$application->setApplicantFirstName( self::APPLICANT_FIRST_NAME );
		$application->setApplicantLastName( self::APPLICANT_LAST_NAME );
		$application->setApplicantSalutation( self::APPLICANT_SALUTATION );
		$application->setApplicantTitle( self::APPLICANT_TITLE );

		$application->setCity( self::APPLICANT_CITY );
		$application->setCountry( self::APPLICANT_COUNTRY_CODE );
		$application->setPostcode( self::APPLICANT_POSTAL_CODE );
		$application->setAddress( self::APPLICANT_STREET_ADDRESS );

		$application->setApplicantEmailAddress( self::APPLICANT_EMAIL_ADDRESS );
		$application->setApplicantPhoneNumber( self::APPLICANT_PHONE_NUMBER );
		$application->setApplicantDateOfBirth( new \DateTime( self::APPLICANT_DATE_OF_BIRTH ) );

		$application->setMembershipType( self::MEMBERSHIP_TYPE );
		$application->setPaymentIntervalInMonths( self::PAYMENT_PERIOD_IN_MONTHS );
		$application->setPaymentAmount( self::PAYMENT_AMOUNT_IN_EURO );

		$application->setPaymentBankAccount( self::PAYMENT_BANK_ACCOUNT );
		$application->setPaymentBankCode( self::PAYMENT_BANK_CODE );
		$application->setPaymentBankName( self::PAYMENT_BANK_NAME );
		$application->setPaymentBic( self::PAYMENT_BIC );
		$application->setPaymentIban( self::PAYMENT_IBAN );

		return $application;
	}

}
