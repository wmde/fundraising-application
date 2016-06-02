<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\ApplyForMembership;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApplicationValidationResult {

	const SOURCE_PAYMENT_AMOUNT = 'amount';
	const SOURCE_IBAN = 'iban';
	const SOURCE_BIC = 'bic';
	const SOURCE_BANK_NAME = 'bank-name';
	const SOURCE_BANK_CODE = 'bank-code';
	const SOURCE_BANK_ACCOUNT = 'bank-account';

	const VIOLATION_TOO_LOW = 'too-low';
	const VIOLATION_WRONG_LENGTH = 'wrong-length';
	const VIOLATION_NOT_MONEY = 'not-money';
	const VIOLATION_MISSING = 'missing';

	private $violations;

	/**
	 * @param string[] $violations ApplicationValidationResult::SOURCE_ => ApplicationValidationResult::VIOLATION_
	 */
	public function __construct( array $violations = [] ) {
		$this->violations = $violations;
	}

	public function getViolations(): array {
		return $this->violations;
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