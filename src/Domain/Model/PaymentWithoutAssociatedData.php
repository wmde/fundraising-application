<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain\Model;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class PaymentWithoutAssociatedData implements PaymentMethod {

	private $paymentType;

	public function __construct( string $paymentType ) {
		$this->paymentType = $paymentType;
	}

	public function getType(): string {
		return $this->paymentType;
	}
}
