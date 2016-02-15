<?php

namespace WMDE\Fundraising\Tests\Unit;

use WMDE\Fundraising\Frontend\Validation\AmountValidator;

/**
 * @covers WMDE\Fundraising\Frontend\Validation\AmountValidator
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class AmountValidatorTest extends \PHPUnit_Framework_TestCase {

	public function testGivenAmountWithinLimits_validationSucceeds() {
		$validator = $this->newAmountValidator();
		$this->assertTrue( $validator->validate( 50 )->isSuccessful() );
	}

	public function testGivenAmountTooLow_validationFails() {
		$validator = $this->newAmountValidator();
		$this->assertFalse( $validator->validate( 0.2 )->isSuccessful() );
	}

	public function testGivenAmountIsNotANumber_validationFails() {
		$validator = $this->newAmountValidator();
		$this->assertFalse( $validator->validate( 'much money' )->isSuccessful() );
	}

	private function newAmountValidator() {
		return new AmountValidator( 1 );
	}
}
