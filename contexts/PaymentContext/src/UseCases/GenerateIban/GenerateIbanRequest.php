<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\PaymentContext\UseCases\GenerateIban;

/**
 * TODO: move to PaymentContext
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen <kai.nissen@wikimedia.de>
 */
class GenerateIbanRequest {

	private $bankAccount;
	private $bankCode;

	public function __construct( string $bankAccount, string $bankCode ) {
		$this->bankAccount = $bankAccount;
		$this->bankCode = $bankCode;
	}

	public function getBankAccount(): string {
		return $this->bankAccount;
	}

	public function getBankCode(): string {
		return $this->bankCode;
	}

}
