<?php

namespace WMDE\Fundraising\Tests\Unit;

use WMDE\Fundraising\Frontend\Domain\PhysicalAddress;
use WMDE\Fundraising\Frontend\Validation\PhysicalAddressValidator;

/**
 * @covers WMDE\Fundraising\Frontend\Validation\PhysicalAddressValidator
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PhysicalAddressValidatorTest extends \PHPUnit_Framework_TestCase {

	public function testGivenValidAddress_validatorReturnsTrue_andConstraintViolationsAreEmpty() {
		$validator = new PhysicalAddressValidator();
		$physicalAddress = new PhysicalAddress();
		$physicalAddress->setStreetAddress( 'Stiftstr. 50' );
		$physicalAddress->setPostalCode( '20099' );
		$physicalAddress->setCity( 'Hamburg' );
		$physicalAddress->setCountryCode( 'DE' );

		$this->assertTrue( $validator->validate( $physicalAddress ) );
		$this->assertEmpty( $validator->getConstraintViolations() );
	}

	public function testGivenEmptyAddress_validatorReturnsFalse_andViolationsContainsRequiredFieldNames() {
		$validator = new PhysicalAddressValidator();
		$physicalAddress = new PhysicalAddress();

		$this->assertFalse( $validator->validate( $physicalAddress ) );
		$this->assertAttributeContains( 'strasse', 'source', $validator->getConstraintViolations()[0] );
		$this->assertAttributeContains( 'plz', 'source', $validator->getConstraintViolations()[1] );
		$this->assertAttributeContains( 'ort', 'source', $validator->getConstraintViolations()[2] );
		$this->assertAttributeContains( 'country', 'source', $validator->getConstraintViolations()[3] );
	}

}
