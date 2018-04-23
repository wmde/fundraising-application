<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\PaymentContext\Domain;

use WMDE\Fundraising\PaymentContext\Domain\Model\Iban;
use WMDE\FunValidators\ConstraintViolation;
use WMDE\FunValidators\ValidationResult;

/**
 * TODO: move to own KontoCheck library?
 *
 * @licence GNU GPL v2+
 * @author Leszek Manicki <leszek.manicki@wikimedia.de>
 * @author Kai Nissen <leszek.manicki@wikimedia.de>
 */
class KontoCheckIbanValidator implements IbanValidator {

	private $bannedIbanNumbers = [];

	public function __construct( array $bannedIbans = [] ) {
		$initializationResult = lut_init();
		if ( $initializationResult !== OK ) {
			throw new KontoCheckLibraryInitializationException( null, $initializationResult );
		}

		$this->bannedIbanNumbers = $bannedIbans;
	}

	public function validate( Iban $value, string $fieldName = '' ): ValidationResult {
		if ( $this->isIbanBlocked( $value ) ) {
			return new ValidationResult( new ConstraintViolation( $value, 'iban_blocked', $fieldName ) );
		}

		if ( iban_check( $value->toString() ) <= 0 ) {
			return new ValidationResult( new ConstraintViolation( $value, 'iban_invalid', $fieldName ) );
		}

		return new ValidationResult();
	}

	public function isIbanBlocked( Iban $iban ): bool {
		return in_array( $iban->toString(), $this->bannedIbanNumbers );
	}

}
