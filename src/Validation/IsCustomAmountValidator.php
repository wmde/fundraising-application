<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Euro\Euro;

class IsCustomAmountValidator {

	/**
	 * @param Euro[] $validAmounts
	 */
	public function __construct( private readonly array $validAmounts ) {
	}

	public function validate( Euro $amount ): bool {
		if ( $amount->getEuroCents() === 0 ) {
			return false;
		}
		return !in_array( $amount, $this->validAmounts );
	}
}
