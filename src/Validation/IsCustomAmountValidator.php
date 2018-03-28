<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Euro\Euro;

class IsCustomAmountValidator {
	private $validAmounts;
	/**
	 * IsCustomAmountValidator constructor.
	 * @param int[] $validAmounts
	 */
	public function __construct( array $validAmounts ) {
		$this->validAmounts = $validAmounts;
	}

	public function validate( Euro $amount ): bool {
		if ( $amount->getEuroCents() === 0 ) {
			return false;
		}
		return !in_array( $amount->getEuroCents(), $this->validAmounts );
	}
}