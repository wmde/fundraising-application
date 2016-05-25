<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain\Model;

use WMDE\Fundraising\Frontend\FreezableValueObject;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Christoph Fischer < christoph.fischer@wikimedia.de >
 */
class BankData {
	use FreezableValueObject;

	private $bic;
	private $iban;
	private $account;
	private $bankCode;
	private $bankName;

	public function getBic(): string {
		return $this->bic;
	}

	public function setBic( string $bic ) {
		$this->assertIsWritable();
		$this->bic = $bic;
		return $this;
	}

	public function getIban(): Iban {
		return $this->iban;
	}

	public function setIban( Iban $iban ) {
		$this->assertIsWritable();
		$this->iban = $iban;
		return $this;
	}

	public function getAccount(): string {
		return $this->account;
	}

	public function setAccount( string $account ) {
		$this->assertIsWritable();
		$this->account = $account;
		return $this;
	}

	public function getBankCode(): string {
		return $this->bankCode;
	}

	public function setBankCode( string $bankCode ) {
		$this->assertIsWritable();
		$this->bankCode = $bankCode;
		return $this;
	}

	public function getBankName(): string {
		return $this->bankName;
	}

	public function setBankName( string $bankName ) {
		$this->assertIsWritable();
		$this->bankName = $bankName;
		return $this;
	}

	public function hasIban(): bool {
		return !empty( $this->getIban()->toString() );
	}

	public function hasCompleteLegacyBankData(): bool {
		return !empty( $this->getAccount() ) || !empty( $this->getBankCode() );
	}

}
