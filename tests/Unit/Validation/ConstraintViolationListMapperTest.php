<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Validation;

use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use WMDE\Fundraising\Frontend\Validation\ConstraintViolationListMapper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \WMDE\Fundraising\Frontend\Validation\ConstraintViolationListMapper
 */
class ConstraintViolationListMapperTest extends TestCase {

	public function testMultipleViolations_canByConvertedToArray(): void {
		$mapper = new ConstraintViolationListMapper();

		$violationOne = new ConstraintViolation( 'not good', null, [], null, '[lorem]', 5 );
		$violationTwo = new ConstraintViolation( 'neither', null, [], null, '[lorem]', 7 );
		$violationThree = new ConstraintViolation( 'oops', null, [], null, '[ipsum]', 9000 );
		$violations = new ConstraintViolationList( [ $violationOne, $violationTwo, $violationThree ] );

		$this->assertEquals( [ 'lorem' => [ 'not good', 'neither' ], 'ipsum' => [ 'oops' ] ], $mapper->map( $violations ) );
	}
}
