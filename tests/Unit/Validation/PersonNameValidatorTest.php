<?php

namespace WMDE\Fundraising\Tests\Unit;

use WMDE\Fundraising\Frontend\Domain\PersonName;
use WMDE\Fundraising\Frontend\Validation\PersonNameValidator;

/**
 * @covers WMDE\Fundraising\Frontend\Validation\PersonNameValidator
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PersonNameValidatorTest extends \PHPUnit_Framework_TestCase {

	public function testGivenValidPersonName_validationSucceeds() {
		$validator = new PersonNameValidator();
		$personName = PersonName::newPrivatePersonName();
		$personName->setSalutation( 'Mr.' );
		$personName->setTitle( '' );
		$personName->setFirstName( 'Hank' );
		$personName->setLastName( 'Scorpio' );

		$this->assertTrue( $validator->validate( $personName ) );
		$this->assertEmpty( $validator->getConstraintViolations() );
	}

	public function testGivenEmptyPersonName_validationFails() {
		$validator = new PersonNameValidator();
		$personName = PersonName::newPrivatePersonName();

		$this->assertFalse( $validator->validate( $personName ) );
		$this->assertSame( 'anrede', $validator->getConstraintViolations()[0]->getSource() );
		$this->assertSame( 'vorname', $validator->getConstraintViolations()[1]->getSource() );
		$this->assertSame( 'nachname', $validator->getConstraintViolations()[2]->getSource() );
	}

	public function testGivenValidCompanyName_validationSucceeds() {
		$validator = new PersonNameValidator();
		$personName = PersonName::newCompanyName();
		$personName->setCompanyName( 'Globex Corp.' );

		$this->assertTrue( $validator->validate( $personName ) );
		$this->assertEmpty( $validator->getConstraintViolations() );
	}

	public function testGivenEmptyCompanyName_validationFails() {
		$validator = new PersonNameValidator();
		$personName = PersonName::newCompanyName();

		$this->assertFalse( $validator->validate( $personName ) );
		$this->assertSame( 'firma', $validator->getConstraintViolations()[0]->getSource() );
	}

}
