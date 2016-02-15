<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\Unit\Validation;

use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;
use WMDE\Fundraising\Frontend\Validation\ValidationResult;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ValidatorTestCase extends \PHPUnit_Framework_TestCase {

	protected function assertConstraintWasViolated( ValidationResult $result, string $fieldName ) {
		$this->assertContainsOnlyInstancesOf( ConstraintViolation::class, $result->getViolations() );
		$this->assertTrue( $result->hasViolations() );

		$violated = false;
		foreach( $result->getViolations() as $violation ) {
			if ( $violation->getSource() === $fieldName ) {
				$violated = true;
			}
		}

		$this->assertTrue( $violated, 'Failed asserting that constraint for field "' . $fieldName . '"" was violated.' );
	}

}
