<?php

namespace WMDE\Fundraising\Tests\Unit;

use WMDE\Fundraising\Frontend\Validation\AmountPolicyValidator;
use WMDE\Fundraising\Frontend\Validation\ValidationResult;

/**
 * @covers WMDE\Fundraising\Frontend\Validation\AmountPolicyValidator
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class AmountPolicyValidatorTest extends \PHPUnit_Framework_TestCase {

	public function testGivenAmountWithinLimits_validationSucceeds() {
		$this->assertTrue( $this->newAmountValidator()->validate( 50, 0 )->isSuccessful() );
	}

	/**
	 * @dataProvider offLimitAmountProvider
	 * @param float $amount
	 * @param int $interval
	 */
	public function testGivenAmountTooHigh_validationFails( $amount, $interval ) {
		$this->assertFalse( $this->newAmountValidator()->validate( $amount, $interval )->isSuccessful() );
	}

	public function offLimitAmountProvider() {
		return [
			[ 1133, 0 ],
			[ 250, 1 ],
			[ 500, 12 ]
		];
	}

	private function newAmountValidator() {
		return new AmountPolicyValidator( 1000, 200, 300 );
	}

}
