<?php

namespace WMDE\Fundraising\Tests\Integration\Validation;

use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donor;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonorAddress;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonorName;
use WMDE\Fundraising\Frontend\DonationContext\Validation\DonorAddressValidator;
use WMDE\Fundraising\Frontend\DonationContext\Validation\DonorValidator;
use WMDE\Fundraising\Frontend\Infrastructure\NullDomainNameValidator;
use WMDE\Fundraising\Frontend\Tests\Unit\Validation\ValidatorTestCase;
use WMDE\Fundraising\Frontend\Validation\EmailValidator;

/**
 * @covers WMDE\Fundraising\Frontend\DonationContext\Validation\DonorValidator
 *
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class DonorValidatorTest extends ValidatorTestCase {

	const VALID_EMAIL_ADDRESS = 'hank.scorpio@globex.com';

	public function testGivenValidPersonalInfo_validationIsSuccessful() {
		$personalInfo = new Donor(
			$this->newCompanyName(),
			$this->newPhysicalAddress(),
			self::VALID_EMAIL_ADDRESS
		);

		$this->assertTrue( $this->newDonorValidator()->validate( $personalInfo )->isSuccessful() );
	}

	public function testGivenMissingEmail_validationFails() {
		$personalInfo = new Donor(
			$this->newCompanyName(),
			$this->newPhysicalAddress(),
			''
		);

		$this->assertFalse( $this->newDonorValidator()->validate( $personalInfo )->isSuccessful() );
	}

	public function testGivenMissingName_validationFails() {
		$personalInfo = new Donor(
			DonorName::newCompanyName(),
			$this->newPhysicalAddress(),
			self::VALID_EMAIL_ADDRESS
		);

		$validator = $this->newDonorValidator();

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

		$validator = $this->newDonorValidator();

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

	private function newPhysicalAddress(): DonorAddress {
		$address = new DonorAddress();
		$address->setStreetAddress( 'PO box 1234' );
		$address->setPostalCode( '90701' );
		$address->setCity( 'Cypress Creek' );
		$address->setCountryCode( 'US' );
		$address->freeze()->assertNoNullFields();
		return $address;
	}

	private function newPhysicalAddressWithMissingData(): DonorAddress {
		$address = new DonorAddress();
		$address->setStreetAddress( '' );
		$address->setPostalCode( '' );
		$address->setCity( 'Cypress Creek' );
		$address->setCountryCode( 'US' );
		$address->freeze()->assertNoNullFields();
		return $address;
	}

	private function newDonorValidator() {
		return new \WMDE\Fundraising\Frontend\DonationContext\Validation\DonorValidator(
			new \WMDE\Fundraising\Frontend\DonationContext\Validation\DonorNameValidator(),
			new DonorAddressValidator(),
			new EmailValidator( new NullDomainNameValidator() )
		);
	}
}