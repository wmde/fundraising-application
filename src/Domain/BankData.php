<?php

namespace WMDE\Fundraising\Frontend\Domain;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Christoph Fischer < christoph.fischer@wikimedia.de >
 */
class BankData {

	private $bic;
	private $iban;
	private $account;
	private $bankCode;
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
