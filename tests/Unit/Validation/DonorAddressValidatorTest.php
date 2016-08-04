<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Tests\Unit;

use WMDE\Fundraising\Frontend\DonatingContext\Domain\Model\DonorAddress;
use WMDE\Fundraising\Frontend\DonatingContext\Validation\DonorAddressValidator;
use WMDE\Fundraising\Frontend\Tests\Unit\Validation\ValidatorTestCase;

/**
 * @covers WMDE\Fundraising\Frontend\DonatingContext\Validation\DonorAddressValidator
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DonorAddressValidatorTest extends ValidatorTestCase {

	public function testGivenValidAddress_validatorReturnsTrue_andConstraintViolationsAreEmpty() {
		$validator = new DonorAddressValidator();
		$physicalAddress = new DonorAddress();
		$physicalAddress->setStreetAddress( 'Stiftstr. 50' );
		$physicalAddress->setPostalCode( '20099' );
		$physicalAddress->setCity( 'Hamburg' );
		$physicalAddress->setCountryCode( 'DE' );
		$physicalAddress->freeze()->assertNoNullFields();

		$this->assertTrue( $validator->validate( $physicalAddress )->isSuccessful() );
	}

	public function testGivenEmptyAddress_validatorReturnsFalse() {
		$validator = new DonorAddressValidator();
		$this->assertFalse( $validator->validate( new DonorAddress() )->isSuccessful() );
	}

	public function testGivenEmptyAddress_violationsContainsRequiredFieldNames() {
		$validator = new DonorAddressValidator();
		$result = $validator->validate( new DonorAddress() );

		$this->assertConstraintWasViolated( $result, 'street' );
		$this->assertConstraintWasViolated( $result, 'postcode' );
		$this->assertConstraintWasViolated( $result, 'city' );
		$this->assertConstraintWasViolated( $result, 'country' );
	}

}
