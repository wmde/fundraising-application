<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\PaymentContext\Domain\Model;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PayPalPayment implements PaymentMethod {

	private $payPalData;

	public function __construct( PayPalData $payPalData ) {
		$this->payPalData = $payPalData;
	}

	public function getType(): string {
		return PaymentType::PAYPAL;
	}

	public function getPayPalData(): PayPalData {
		return $this->payPalData;
	}

	public function addPayPalData( PayPalData $palPayData ) {
		$this->payPalData = $palPayData;
	}
}