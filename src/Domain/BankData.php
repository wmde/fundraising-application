<?php

namespace WMDE\Fundraising\Frontend\Domain;

/**
 * @licence GNU GPL v2+
 * @author Christoph Fischer <christoph.fischer@wikimedia.de >
 */
class BankData {
	/** @var string */
	private $bic;

	/** @var string */
	private $iban;

	/** @var string */
	private $account;

	/** @var string */
	private $bankCode;

	/** @var string */
	private $bankName;

	public function getBic(): string {
		return $this->bic;
	}

	public function setBic( string $bic ) {
		$this->bic = $bic;
	}

	public function getIban(): string {
		return $this->iban;
	}

	public function setIban( string $iban ) {
		$this->iban = $iban;
	}

	public function getAccount(): string {
		return $this->account;
	}

	public function setAccount( string $account ) {
		$this->account = $account;
	}

	public function getBankCode(): string {
		return $this->bankCode;
	}

	public function setBankCode( string $bankCode ) {
		$this->bankCode = $bankCode;
	}

	public function getBankName(): string {
		return $this->bankName;
	}

	public function setBankName( string $bankName ) {
		$this->bankName = $bankName;
	}

	public function getBankData() {
		return get_object_vars( $this );
	}
}
