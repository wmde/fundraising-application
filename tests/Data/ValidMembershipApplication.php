<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Data;

use WMDE\Fundraising\Entities\MembershipApplication as DoctrineMembershipApplication;
use WMDE\Fundraising\Frontend\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\Domain\Model\Euro;
use WMDE\Fundraising\Frontend\Domain\Model\Iban;
use WMDE\Fundraising\Frontend\Domain\Model\MembershipApplicant;
use WMDE\Fundraising\Frontend\Domain\Model\MembershipApplication;
use WMDE\Fundraising\Frontend\Domain\Model\MembershipPayment;
use WMDE\Fundraising\Frontend\Domain\Model\PersonName;
use WMDE\Fundraising\Frontend\Domain\Model\PhysicalAddress;

/**
 * newDomainEntity and newDoctrineEntity return equivalent objects.
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ValidMembershipApplication {

	const APPLICANT_FIRST_NAME = 'Jeroen';
	const APPLICANT_LAST_NAME = 'De Dauw';
	const APPLICANT_SALUTATION = 'salutation';
	const APPLICANT_TITLE = 'title';

	const APPLICANT_DATE_OF_BIRTH = '1990-01-01';

	const APPLICANT_CITY = 'Berlin';
	const APPLICANT_COUNTRY_CODE = 'DE';
	const APPLICANT_POSTAL_CODE = '1234	';
	const APPLICANT_STREET_ADDRESS = 'Nyan street';

	const APPLICANT_EMAIL_ADDRESS = 'jeroendedauw@gmail.com';

	const MEMBERSHIP_TYPE = MembershipApplication::ACTIVE_MEMBERSHIP;
	const PAYMENT_PERIOD_IN_MONTHS = 3;
	const PAYMENT_AMOUNT_IN_EURO = 5;

	const PAYMENT_BANK_ACCOUNT = '0648489890';
	const PAYMENT_BANK_CODE = '50010517';
	const PAYMENT_BANK_NAME = 'ING-DiBa';
	const PAYMENT_BIC = 'INGDDEFFXXX';
	const PAYMENT_IBAN = 'DE12500105170648489890';

	public static function newDomainEntity(): MembershipApplication {
		return ( new self() )->createApplication();
	}

	private function createApplication(): MembershipApplication {
		return new MembershipApplication(
			null,
			self::MEMBERSHIP_TYPE,
			new MembershipApplicant(
				$this->newPersonName(),
				$this->newAddress(),
				self::APPLICANT_EMAIL_ADDRESS,
				new \DateTime( self::APPLICANT_DATE_OF_BIRTH )
			),
			new MembershipPayment(
				self::PAYMENT_PERIOD_IN_MONTHS,
				Euro::newFromFloat( self::PAYMENT_AMOUNT_IN_EURO ),
				$this->newBankData()
			)
		);
	}

	private function newPersonName(): PersonName {
		$personName = PersonName::newPrivatePersonName();

		$personName->setFirstName( self::APPLICANT_FIRST_NAME );
		$personName->setLastName( self::APPLICANT_LAST_NAME );
		$personName->setSalutation( self::APPLICANT_SALUTATION );
		$personName->setTitle( self::APPLICANT_TITLE );

		return $personName->freeze()->assertNoNullFields();
	}

	private function newAddress(): PhysicalAddress {
		$address = new PhysicalAddress();

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

		$application->setApplicantDateOfBirth( new \DateTime( self::APPLICANT_DATE_OF_BIRTH ) );

		$application->setCity( self::APPLICANT_CITY );
		$application->setCountry( self::APPLICANT_COUNTRY_CODE );
		$application->setPostcode( self::APPLICANT_POSTAL_CODE );
		$application->setAddress( self::APPLICANT_STREET_ADDRESS );

		$application->setApplicantEmailAddress( self::APPLICANT_EMAIL_ADDRESS );

		$application->setMembershipType( self::MEMBERSHIP_TYPE );
		$application->setPaymentIntervalInMonths( self::PAYMENT_PERIOD_IN_MONTHS );
		$application->setPaymentAmount( self::PAYMENT_AMOUNT_IN_EURO );

		$application->setPaymentBankAccount( self::PAYMENT_BANK_ACCOUNT );
		$application->setPaymentBankCode( self::PAYMENT_BANK_CODE );
		$application->setPaymentBankName( self::PAYMENT_BANK_NAME );
		$application->setPaymentBic( self::PAYMENT_BIC );
		$application->setPaymentIban( self::PAYMENT_IBAN );

		// TODO: data fields?
		$application->encodeAndSetData( [] );

		return $application;
	}

}
