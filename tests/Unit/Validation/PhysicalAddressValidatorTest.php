<?php

namespace WMDE\Fundraising\Tests\Unit;

use WMDE\Fundraising\Frontend\Domain\PhysicalAddress;
use WMDE\Fundraising\Frontend\Tests\Unit\Validation\ValidatorTestCase;
use WMDE\Fundraising\Frontend\Validation\PhysicalAddressValidator;

/**
 * @covers WMDE\Fundraising\Frontend\Validation\PhysicalAddressValidator
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PhysicalAddressValidatorTest extends ValidatorTestCase {

	public function testGivenValidAddress_validatorReturnsTrue_andConstraintViolationsAreEmpty() {
		$validator = new PhysicalAddressValidator();
		$physicalAddress = new PhysicalAddress();
		$physicalAddress->setStreetAddress( 'Stiftstr. 50' );
		$physicalAddress->setPostalCode( '20099' );
		$physicalAddress->setCity( 'Hamburg' );
		$physicalAddress->setCountryCode( 'DE' );
		$physicalAddress->freeze()->assertNoNullFields();

		$this->assertTrue( $validator->validate( $physicalAddress )->isSuccessful() );
	}

	public function testGivenEmptyAddress_validatorReturnsFalse() {
		$validator = new PhysicalAddressValidator();
		$this->assertFalse( $validator->validate( new PhysicalAddress() )->isSuccessful() );
	}

	public function testGivenEmptyAddress_violationsContainsRequiredFieldNames() {
		$validator = new PhysicalAddressValidator();
		$result = $validator->validate( new PhysicalAddress() );

		$this->assertConstraintWasViolated( $result, 'strasse' );
		$this->assertConstraintWasViolated( $result, 'plz' );
		$this->assertConstraintWasViolated( $result, 'ort' );
		$this->assertConstraintWasViolated( $result, 'country' );
	}

}
