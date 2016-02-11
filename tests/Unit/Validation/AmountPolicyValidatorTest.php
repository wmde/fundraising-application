<?php

namespace WMDE\Fundraising\Tests\Unit;

use WMDE\Fundraising\Frontend\Validation\AmountPolicyValidator;

/**
 * @covers WMDE\Fundraising\Frontend\Validation\AmountPolicyValidator
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class AmountPolicyValidatorTest extends \PHPUnit_Framework_TestCase {

	public function testGivenAmountWithinLimits_validationSucceeds() {
		$validator = $this->newAmountValidator();
		$this->assertTrue( $validator->validate( 50, 0 ) );
	}

	public function offLimitAmountProvider() {
		return [
			[ 1133, 0 ],
			[ 250, 1 ],
			[ 500, 12 ]
		];
	}

	/**
	 * @dataProvider offLimitAmountProvider
	 * @param float $amount
	 * @param int $interval
	 */
	public function testGivenAmountTooHigh_validationFails( $amount, $interval ) {
		$validator = $this->newAmountValidator();
		$this->assertFalse( $validator->validate( $amount, $interval ) );
	}

	private function newAmountValidator() {
		return new AmountPolicyValidator( 1000, 200, 300 );
	}
}
