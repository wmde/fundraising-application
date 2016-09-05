<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\PaymentContext\Domain\Model;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class BankTransferPayment implements PaymentMethod {

	private $bankTransferCode;

	public function __construct( string $bankTransferCode ) {
		$this->bankTransferCode = $bankTransferCode;
	}

	public function getType(): string {
		return PaymentType::BANK_TRANSFER;
	}

	public function getBankTransferCode(): string {
		return $this->bankTransferCode;
	}

}