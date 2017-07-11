<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\PaymentContext\Domain\Model;

class SofortPayment implements PaymentMethod {

	private $bankTransferCode;

	public function __construct( string $bankTransferCode ) {
		$this->bankTransferCode = $bankTransferCode;
	}

	public function getType(): string {
		return PaymentType::SOFORT;
	}

	public function getBankTransferCode(): string {
		return $this->bankTransferCode;
	}
}
