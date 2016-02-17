<?php

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\BankDataConverter;
use WMDE\Fundraising\Frontend\Domain\Iban;

/**
 * @licence GNU GPL v2+
 * @author Leszek Manicki <leszek.manicki@wikimedia.de>
 * @author Kai Nissen <leszek.manicki@wikimedia.de>
 */
class IbanValidator {

	private $bankDataConverter;
	private $bannedIbanNumbers = [];

	/**
	 * @param BankDataConverter $bankDataConverter
	 * @param string[] $bannedIbans
	 */
	public function __construct( BankDataConverter $bankDataConverter, array $bannedIbans = [] ) {
		$this->bankDataConverter = $bankDataConverter;
		$this->bannedIbanNumbers = $bannedIbans;
	}

	public function validate( Iban $value, string $fieldName = '' ): ValidationResult {
		if ( in_array( $value->toString(), $this->bannedIbanNumbers ) ) {
			return new ValidationResult( new ConstraintViolation( $value, 'IBAN is blocked', $fieldName ) );
		}

		if ( !$this->bankDataConverter->validateIban( $value ) ) {
			return new ValidationResult( new ConstraintViolation( $value, 'IBAN is not valid', $fieldName ) );
		}

		return new ValidationResult();
	}

}
