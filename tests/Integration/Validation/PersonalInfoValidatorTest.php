<?php

namespace WMDE\Fundraising\Tests\Integration\Validation;

use WMDE\Fundraising\Frontend\DonatingContext\Domain\Model\DonorName;
use WMDE\Fundraising\Frontend\Domain\Model\PhysicalAddress;
use WMDE\Fundraising\Frontend\DonatingContext\Domain\Model\Donor;
use WMDE\Fundraising\Frontend\Infrastructure\NullDomainNameValidator;
use WMDE\Fundraising\Frontend\Tests\Unit\Validation\ValidatorTestCase;
use WMDE\Fundraising\Frontend\Validation\EmailValidator;
use WMDE\Fundraising\Frontend\Validation\PersonalInfoValidator;
use WMDE\Fundraising\Frontend\Validation\PersonNameValidator;
use WMDE\Fundraising\Frontend\Validation\PhysicalAddressValidator;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class PersonalInfoValidatorTest extends ValidatorTestCase {

	const VALID_EMAIL_ADDRESS = 'hank.scorpio@globex.com';

	public function testGivenValidPersonalInfo_validationIsSuccessful() {
		$personalInfo = new Donor(
			$this->newCompanyName(),
			$this->newPhysicalAddress(),
			self::VALID_EMAIL_ADDRESS
		);

		$this->assertTrue( $this->newPersonalInfoValidator()->validate( $personalInfo )->isSuccessful() );
	}

	public function testGivenMissingEmail_validationFails() {
		$personalInfo = new Donor(
			$this->newCompanyName(),
			$this->newPhysicalAddress(),
			''
		);

		$this->assertFalse( $this->newPersonalInfoValidator()->validate( $personalInfo )->isSuccessful() );
	}

	public function testGivenMissingName_validationFails() {
		$personalInfo = new Donor(
			DonorName::newCompanyName(),
			$this->newPhysicalAddress(),
			self::VALID_EMAIL_ADDRESS
		);

		$validator = $this->newPersonalInfoValidator();

		$this->assertFalse( $validator->validate( $personalInfo )->isSuccessful() );
		$this->assertConstraintWasViolated(
			$validator->validate( $personalInfo ),
			'company'
		);
	}

	public function testGivenMissingAddressFields_validationFails() {
		$personalInfo = new Donor(
			$this->newCompanyName(),
			$this->newPhysicalAddressWithMissingData(),
			self::VALID_EMAIL_ADDRESS
		);

		$validator = $this->newPersonalInfoValidator();

		$this->assertFalse( $validator->validate( $personalInfo )->isSuccessful() );
		$this->assertConstraintWasViolated(
			$validator->validate( $personalInfo ),
			'street'
		);
		$this->assertConstraintWasViolated(
			$validator->validate( $personalInfo ),
			'postcode'
		);
	}

	private function newCompanyName(): DonorName {
		$name = DonorName::newCompanyName();
		$name->setCompanyName( 'Globex Corp.' );
		return $name;
	}

	private function newPhysicalAddress(): PhysicalAddress {
		$address = new PhysicalAddress();
		$address->setStreetAddress( 'PO box 1234' );
		$address->setPostalCode( '90701' );
		$address->setCity( 'Cypress Creek' );
		$address->setCountryCode( 'US' );
		$address->freeze()->assertNoNullFields();
		return $address;
	}

	private function newPhysicalAddressWithMissingData(): PhysicalAddress {
		$address = new PhysicalAddress();
		$address->setStreetAddress( '' );
		$address->setPostalCode( '' );
		$address->setCity( 'Cypress Creek' );
		$address->setCountryCode( 'US' );
		$address->freeze()->assertNoNullFields();
		return $address;
	}

	private function newPersonalInfoValidator() {
		return new PersonalInfoValidator(
			new PersonNameValidator(),
			new PhysicalAddressValidator(),
			new EmailValidator( new NullDomainNameValidator() )
		);
	}
}