<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Domain\PaymentData;

use WMDE\Fundraising\Frontend\Domain\PaymentData;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DirectDebitPaymentData extends PaymentData implements PaymentType {

	private $bic;
	private $iban;
	private $account;
	private $bankCode;
	private $bankName;

	public function getPaymentType() {
		return self::PAYMENT_TYPE_DIRECT_DEBIT;
	}

	public function getBic(): string {
		return $this->bic;
	}

	public function setBic( string $bic ) {
		$this->bic = $bic;

		return $this;
	}

	public function getIban(): string {
		return $this->iban;
	}

	public function setIban( string $iban ) {
		$this->iban = $iban;

		return $this;
	}

	public function getAccount(): string {
		return $this->account;
	}

	public function setAccount( string $account ) {
		$this->account = $account;

		return $this;
	}

	public function getBankCode(): string {
		return $this->bankCode;
	}

	public function setBankCode( string $bankCode ) {
		$this->bankCode = $bankCode;

		return $this;
	}

	public function getBankName(): string {
		return $this->bankName;
	}

	public function setBankName( string $bankName ) {
		$this->bankName = $bankName;

		return $this;
	}

}
