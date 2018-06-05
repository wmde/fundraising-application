<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Payment;

use WMDE\Fundraising\PaymentContext\Domain\IbanValidator;
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

	public function __construct() {
		$initializationResult = lut_init();
		if ( $initializationResult !== OK ) {
			throw new KontoCheckLibraryInitializationException( null, $initializationResult );
		}
	}

	public function validate( Iban $value, string $fieldName = '' ): ValidationResult {
		if ( iban_check( $value->toString() ) <= 0 ) {
			return new ValidationResult( new ConstraintViolation( $value, 'iban_invalid', $fieldName ) );
		}

		return new ValidationResult();
	}
}
