<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain\Model;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface PaymentMethod {

	/**
	 * @return string Element of the PaymentType:: enum
	 */
	public function getType(): string;

}
