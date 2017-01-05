<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\BankData;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class BankDataValidator {
	use CanValidateField;

	private $ibanValidator;

	public function __construct( IbanValidator $ibanValidator ) {
		$this->ibanValidator = $ibanValidator;
	}

	public function validate( BankData $bankData ): ValidationResult {
		$validator = new RequiredFieldValidator();
		$violations = [];

		$violations[] = $this->getFieldViolation( $validator->validate( $bankData->getIban()->toString() ), 'iban' );
		$violations[] = $this->getFieldViolation( $validator->validate( $bankData->getBic() ), 'bic' );

		if ( $bankData->getIban()->getCountryCode() === 'DE' ) {
			$stringLengthValidator = new StringLengthValidator();
			$violations[] = $this->getFieldViolation( $validator->validate( $bankData->getAccount() ), 'konto' );
			$violations[] = $this->getFieldViolation( $validator->validate( $bankData->getBankCode() ), 'blz' );
			$violations[] = $this->getFieldViolation( $stringLengthValidator->validate( $bankData->getAccount(), 10 ), 'konto' );
			$violations[] = $this->getFieldViolation( $stringLengthValidator->validate( $bankData->getBankCode(), 8 ), 'blz' );
		}

		$violations[] = $this->getFieldViolation( $this->ibanValidator->validate( $bankData->getIban() ), 'iban' );

		return new ValidationResult( ...array_filter( $violations ) );
	}

}
