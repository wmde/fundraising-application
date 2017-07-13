<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\PaymentContext\Domain\Model;

use DateTime;

class SofortPayment implements PaymentMethod {

	/**
	 * @var string
	 */
	private $bankTransferCode = '';
	/**
	 * @var DateTime|null
	 */
	private $confirmedAt;

	public function __construct( string $bankTransferCode ) {
		$this->bankTransferCode = $bankTransferCode;
	}

	public function getType(): string {
		return PaymentType::SOFORT;
	}

	public function getBankTransferCode(): string {
		return $this->bankTransferCode;
	}

	public function getConfirmedAt(): ?DateTime {
		return $this->confirmedAt;
	}

	public function setConfirmedAt( ?DateTime $confirmedAt ): void {
		$this->confirmedAt = $confirmedAt;
	}
}
