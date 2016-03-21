<?php

namespace WMDE\Fundraising\Tests\Integration\Validation;

use WMDE\Fundraising\Frontend\Domain\Model\PersonalInfo;
use WMDE\Fundraising\Frontend\Domain\Model\PersonName;
use WMDE\Fundraising\Frontend\Domain\Model\PhysicalAddress;
use WMDE\Fundraising\Frontend\Domain\NullDomainNameValidator;
use WMDE\Fundraising\Frontend\Tests\Unit\Validation\ValidatorTestCase;
use WMDE\Fundraising\Frontend\Validation\MailValidator;
use WMDE\Fundraising\Frontend\Validation\PersonalInfoValidator;
use WMDE\Fundraising\Frontend\Validation\PersonNameValidator;
use WMDE\Fundraising\Frontend\Validation\PhysicalAddressValidator;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class PersonalInfoValidatorTest extends ValidatorTestCase {

	public function testGivenValidPersonalInfo_validationIsSuccessful() {
		$personalInfo = new PersonalInfo(
			$this->newCompanyName(),
			$this->newPhysicalAddress(),
			'hank.scorpio@globex.com'
		);

		$this->assertTrue( $this->newPersonalInfoValidator()->validate( $personalInfo )->isSuccessful() );
	}

	public function testGivenMissingEmail_validationFails() {
		$personalInfo = new PersonalInfo(
			$this->newCompanyName(),
			$this->newPhysicalAddress(),
			''
		);

		$this->assertFalse( $this->newPersonalInfoValidator()->validate( $personalInfo )->isSuccessful() );
	}

	public function testGivenMissingName_validationFails() {
		$personalInfo = new PersonalInfo(
			PersonName::newCompanyName(),
			$this->newPhysicalAddress(),
			'hank.scorpio@globex.com'
		);

		$validator = $this->newPersonalInfoValidator();

		$this->assertFalse( $validator->validate( $personalInfo )->isSuccessful() );
		$this->assertConstraintWasViolated(
			$validator->validate( $personalInfo ),
			'firma'
		);
	}

	public function testGivenMissingAddressFields_validationFails() {
		$personalInfo = new PersonalInfo(
			$this->newCompanyName(),
			$this->newPhysicalAddressWithMissingData(),
			'hank.scorpio@globex.com'
		);

		$validator = $this->newPersonalInfoValidator();

		$this->assertFalse( $validator->validate( $personalInfo )->isSuccessful() );
		$this->assertConstraintWasViolated(
			$validator->validate( $personalInfo ),
			'strasse'
		);
		$this->assertConstraintWasViolated(
			$validator->validate( $personalInfo ),
			'plz'
		);
	}

	private function newCompanyName(): PersonName {
		$name = PersonName::newCompanyName();
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
			new MailValidator( new NullDomainNameValidator() )
		);
	}
}