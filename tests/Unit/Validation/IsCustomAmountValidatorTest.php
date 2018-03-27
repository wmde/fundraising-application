<?php
/**
 * Created by IntelliJ IDEA.
 * User: tozh
 * Date: 27.03.18
 * Time: 15:57
 */

namespace WMDE\Fundraising\Frontend\Tests\Unit\Validation;

use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\Validation\IsCustomAmountValidator;
use PHPUnit\Framework\TestCase;

class IsCustomAmountValidatorTest extends TestCase
{

	public function testValidate()
	{
		$validator = new IsCustomAmountValidator( [ 500, 1500, 2500, 5000, 7500, 10000, 25000, 30000 ] );
		$amount = Euro::newFromCents( 500 );
		$this->assertFalse( $validator->validate( $amount ) );
	}

	public function testValidateWithZero()
	{
		$validator = new IsCustomAmountValidator( [ 500, 1500, 2500, 5000, 7500, 10000, 25000, 30000 ] );
		$amount = Euro::newFromCents( 0 );
		$this->assertFalse( $validator->validate( $amount ) );
	}

	public function testValidateWithRandomCommaAmount_returnsTrue()
	{
		$validator = new IsCustomAmountValidator( [ 500, 1500, 2500, 5000, 7500, 10000, 25000, 30000 ] );
		$amount = Euro::newFromCents( 4711 );
		$this->assertTrue( $validator->validate( $amount ) );
	}
}
