<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Validation;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Infrastructure\Validation\ValidationErrorLogger;
use WMDE\PsrLogTestDoubles\LoggerSpy;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\Validation\ValidationErrorLogger
 */
class ValidationErrorLoggerTest extends TestCase {

	public function testGivenEmptyValues_doesNotLog() {
		$loggerSpy = new LoggerSpy();
		$fields = [ 'first_name', 'last_name', 'email', 'postcode', 'country' ];
		$validationErrorLogger = new ValidationErrorLogger( $loggerSpy );
		$violations = $this->createViolationErrors( $fields );

		$validationErrorLogger->logViolations( 'Test error.', $fields, $violations );

		// The logger spy throws an assertion
		$loggerSpy->assertNoLoggingCallsWhereMade();
		// This is a hacky way of making phpunit happy
		$this->assertTrue( true );
	}

	/**
	 * @param string[] $fieldNames
	 *
	 * @return array<string, array<int, string>>
	 */
	private function createViolationErrors( array $fieldNames ): array {
		$violations = [];

		foreach ( $fieldNames as $field ) {
			$violations['validation_errors'][] = "Validation field '{$field}' with value '' failed with: This is a required field";
		}

		return $violations;
	}

	public function testGivenInvalidNonLegacyFields_theyGetLogged() {
		$loggerSpy = new LoggerSpy();
		$fields = [ 'first_name', 'last_name' ];
		$validationErrorLogger = new ValidationErrorLogger( $loggerSpy );
		$violations = $this->createViolationErrors( $fields );

		$validationErrorLogger->logViolations( 'Test error.', $fields, $violations );

		$firstCallContext = $loggerSpy->getFirstLogCall()->getContext();

		$this->assertSame( 1, $loggerSpy->getLogCalls()->count() );
		$this->assertStringContainsString( 'first_name', $firstCallContext['validation_errors'][0] );
		$this->assertStringContainsString( 'last_name', $firstCallContext['validation_errors'][1] );
	}
}
