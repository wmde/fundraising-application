<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\PaymentContext\Domain;

use WMDE\Fundraising\PaymentContext\Domain\Model\Iban;
use WMDE\FunValidators\ConstraintViolation;
use WMDE\FunValidators\ValidationResult;

/**
 * @licence GNU GPL v2+
 * @author Leszek Manicki <leszek.manicki@wikimedia.de>
 * @author Kai Nissen <leszek.manicki@wikimedia.de>
 */
class KontoCheckIbanValidator implements IbanValidator {

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
		if ( $this->isIbanBlocked( $value ) ) {
			return new ValidationResult( new ConstraintViolation( $value, 'iban_blocked', $fieldName ) );
		}

		if ( !$this->bankDataConverter->validateIban( $value ) ) {
			return new ValidationResult( new ConstraintViolation( $value, 'iban_invalid', $fieldName ) );
		}

		return new ValidationResult();
	}

	public function isIbanBlocked( Iban $iban ): bool {
		return in_array( $iban->toString(), $this->bannedIbanNumbers );
	}

}
