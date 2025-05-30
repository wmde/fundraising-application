<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\Validation;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Infrastructure\Validation\ValidationErrorLogger;
use WMDE\Fundraising\Frontend\Tests\Fixtures\LoggerSpy;

#[CoversClass( ValidationErrorLogger::class )]
class ValidationErrorLoggerTest extends TestCase {

	#[DoesNotPerformAssertions]
	public function testGivenEmptyValues_doesNotLog(): void {
		$loggerSpy = new LoggerSpy();
		$fields = [ 'first_name', 'last_name', 'email', 'postcode', 'country' ];
		$validationErrorLogger = new ValidationErrorLogger( $loggerSpy );
		$violations = $this->createViolationErrors( $fields );

		$validationErrorLogger->logViolations( 'Test error.', $fields, $violations );

		// The logger spy throws an assertion
		$loggerSpy->assertNoLoggingCallsWhereMade();
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

	public function testGivenInvalidNonLegacyFields_theyGetLogged(): void {
		$loggerSpy = new LoggerSpy();
		$fields = [ 'first_name', 'last_name' ];
		$validationErrorLogger = new ValidationErrorLogger( $loggerSpy );
		$violations = $this->createViolationErrors( $fields );

		$validationErrorLogger->logViolations( 'Test error.', $fields, $violations );

		$firstCallContext = $loggerSpy->getFirstLogCall()->getContext();

		$this->assertCount( 1, $loggerSpy->getLogCalls() );
		/** @var string[] $firstCallContextValidationErrors */
		$firstCallContextValidationErrors = $firstCallContext['validation_errors'];
		$this->assertStringContainsString( 'first_name', $firstCallContextValidationErrors[0] );
		$this->assertStringContainsString( 'last_name', $firstCallContextValidationErrors[1] );
	}
}
