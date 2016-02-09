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

	public function testGivenEmptyAddress_validatorReturnsFalse() {
		$validator = new PhysicalAddressValidator();
		$this->assertFalse( $validator->validate( new PhysicalAddress() ) );
	}

	public function testGivenEmptyAddress_violationsContainsRequiredFieldNames() {
		$validator = new PhysicalAddressValidator();
		$validator->validate( new PhysicalAddress() );

		$this->assertSame( 'strasse', $validator->getConstraintViolations()[0]->getSource() );
		$this->assertSame( 'plz', $validator->getConstraintViolations()[1]->getSource() );
		$this->assertSame( 'ort', $validator->getConstraintViolations()[2]->getSource() );
		$this->assertSame( 'country', $validator->getConstraintViolations()[3]->getSource() );
	}

}
