<?php

namespace WMDE\Fundraising\Tests\Unit;

use WMDE\Fundraising\Frontend\Domain\Donation;
use WMDE\Fundraising\Frontend\Domain\PersonalInfo;
use WMDE\Fundraising\Frontend\Domain\NullDomainNameValidator;
use WMDE\Fundraising\Frontend\Domain\PersonName;
use WMDE\Fundraising\Frontend\Domain\PhysicalAddress;
use WMDE\Fundraising\Frontend\MailAddress;
use WMDE\Fundraising\Frontend\Tests\Unit\Validation\ValidatorTestCase;
use WMDE\Fundraising\Frontend\Validation\AmountPolicyValidator;
use WMDE\Fundraising\Frontend\Validation\AmountValidator;
use WMDE\Fundraising\Frontend\Validation\DonationValidator;
use WMDE\Fundraising\Frontend\Validation\MailValidator;
use WMDE\Fundraising\Frontend\Validation\PersonNameValidator;
use WMDE\Fundraising\Frontend\Validation\PhysicalAddressValidator;

/**
 * @covers WMDE\Fundraising\Frontend\Validation\DonationValidator
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DonationValidatorTest extends ValidatorTestCase {

	/** @var DonationValidator */
	private $donationValidator;

	public function setUp() {
		$this->donationValidator = $this->newDonationValidator();
	}

	public function testGivenValidDonation_validatorReturnsTrue() {
		$personalInfo = new PersonalInfo();
		$personalInfo->setPersonName( $this->newCompanyName() );
		$personalInfo->setPhysicalAddress( $this->newPhysicalAddress() );
		$personalInfo->setEmailAddress( new MailAddress( 'hank.scorpio@globex.com' ) );
		$personalInfo->freeze()->assertNoNullFields();

		$donation = new Donation();
		$donation->setAmount( 1 );
		$donation->setPersonalInfo( $personalInfo );
		$donation->freeze()->assertNoNullFields();

		$this->assertTrue( $this->donationValidator->validate( $donation ) );
	}

	public function testNoPersonalInfoGiven_validatorReturnsTrue() {
		$donation = new Donation();
		$donation->setAmount( 1 );
		$this->assertTrue( $this->donationValidator->validate( $donation ) );
	}

	public function testPartlyPersonalInfoGiven_validatorReturnsFalse() {
		$personalInfo = new PersonalInfo();
		$personalInfo->setPersonName( PersonName::newCompanyName() );
		$personalInfo->setPhysicalAddress( new PhysicalAddress() );
		$personalInfo->setEmailAddress( new MailAddress( 'hank.scorpio@globex.com' ) );
		$personalInfo->freeze()->assertNoNullFields();

		$donation = new Donation();
		$donation->setAmount( 1 );
		$donation->setPersonalInfo( $personalInfo );
		$donation->freeze()->assertNoNullFields();

		$this->assertFalse( $this->donationValidator->validate( $donation ) );
		$this->assertConstraintWasViolated( $this->donationValidator->getConstraintViolations(), 'firma' );
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

	private function newDonationValidator(): DonationValidator {
		return new DonationValidator(
			new AmountValidator( 1 ),
			new AmountPolicyValidator( 1000, 200, 300 ),
			new PersonNameValidator(),
			new PhysicalAddressValidator(),
			new MailValidator( new NullDomainNameValidator() )
		);
	}

}
