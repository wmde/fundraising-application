<?php
/**
 * Created by IntelliJ IDEA.
 * User: tozh
 * Date: 27.03.18
 * Time: 15:33
 */

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Euro\Euro;

class IsCustomAmountValidator
{
	private $validAmounts;
	/**
	 * AmountInArrayValidator constructor.
	 */
	public function __construct( array $validAmounts )
	{
		$this->validAmounts = $validAmounts;
	}

	public function validate( Euro $amount ):bool {
		if ( $amount->getEuroCents() === 0 ) {
			return false;
		}
		return !in_array( $amount->getEuroCents(), $this->validAmounts );
	}
}