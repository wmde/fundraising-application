<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\PaymentContext\Domain\Model;

class SofortPayment implements PaymentMethod {

	private $uuid;

	public function __construct( string $uuid ) {
		$this->uuid = $uuid;
	}

	public function getType(): string {
		return PaymentType::SOFORT;
	}

	public function getUuid(): string {
		return $this->uuid;
	}
}
