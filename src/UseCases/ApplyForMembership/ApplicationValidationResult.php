<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\ApplyForMembership;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApplicationValidationResult {

	const SOURCE_PAYMENT_AMOUNT = 'amount';

	const VIOLATION_TOO_LOW = 'too-low';
	const VIOLATION_NOT_MONEY = 'not-money';

	private $violations;

	/**
	 * @param string[] $violations
	 */
	public function __construct( array $violations = [] ) {
		$this->violations = $violations;
	}

	public function isSuccessful(): bool {
		return empty( $this->violations );
	}

	/**
	 * @return string[]
	 */
	public function getViolationSources(): array {
		return array_keys( $this->violations );
	}

	/**
	 * @param string $source
	 *
	 * @return string
	 * @throws \OutOfBoundsException
	 */
	public function getViolationType( string $source ): string {
		if ( array_key_exists( $source, $this->violations ) ) {
			 return $this->violations[$source];
		}

		throw new \OutOfBoundsException();
	}

}