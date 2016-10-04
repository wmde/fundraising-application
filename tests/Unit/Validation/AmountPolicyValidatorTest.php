<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Tests\Unit;

use WMDE\Fundraising\Frontend\Validation\AmountPolicyValidator;

/**
 * @covers WMDE\Fundraising\Frontend\Validation\AmountPolicyValidator
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class AmountPolicyValidatorTest extends \PHPUnit_Framework_TestCase {

	const INTERVAL_ONCE = 0;
	const INTERVAL_MONTHLY = 1;
	const INTERVAL_QUARTERLY = 3;
	const INTERVAL_SEMIANNUAL = 6;
	const INTERVAL_YEARLY = 12;

	/**
	 * @dataProvider smallAmountProvider
	 * @param float $amount
	 * @param int $interval
	 */
	public function testGivenAmountWithinLimits_validationSucceeds( float $amount, int $interval ) {
		$this->assertTrue( $this->newAmountValidator()->validate( 50, 0 )->isSuccessful() );
	}

	public function smallAmountProvider() {
		return [
			[ 750.0, self::INTERVAL_ONCE ],
			[ 20.0, self::INTERVAL_MONTHLY ],
			[ 100.5, self::INTERVAL_QUARTERLY ],
			[ 499.98, self::INTERVAL_SEMIANNUAL ],
			[ 999.99, self::INTERVAL_YEARLY ]
		];
	}

	/**
	 * @dataProvider offLimitAmountProvider
	 * @param float $amount
	 * @param int $interval
	 */
	public function testGivenAmountTooHigh_validationFails( float $amount, int $interval ) {
		$this->assertFalse( $this->newAmountValidator()->validate( $amount, $interval )->isSuccessful() );
	}

	public function offLimitAmountProvider() {
		return [
			[ 1750.0, self::INTERVAL_ONCE ],
			[ 101.0, self::INTERVAL_MONTHLY ],
			[ 250.5, self::INTERVAL_QUARTERLY ],
			[ 600, self::INTERVAL_SEMIANNUAL ],
			[ 1337, self::INTERVAL_YEARLY ]
		];
	}

	private function newAmountValidator() {
		return new AmountPolicyValidator( 1000, 1000 );
	}

}
