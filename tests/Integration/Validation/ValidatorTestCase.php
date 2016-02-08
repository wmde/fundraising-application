<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\Integration\Validation;

use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ValidatorTestCase extends \PHPUnit_Framework_TestCase {

	/**
	 * @param ConstraintViolation[] $violations
	 * @param string $fieldName
	 */
	protected function assertConstraintWasViolated( array $violations, string $fieldName ) {
		$this->assertCount( 1, $violations );
		$this->assertContainsOnlyInstancesOf( ConstraintViolation::class, $violations );
		$this->assertEquals( $fieldName, current( $violations )->getSource() );
	}

}
