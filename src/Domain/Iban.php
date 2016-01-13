<?php

namespace WMDE\Fundraising\Frontend\Domain;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen <kai.nissen@wikimedia.de>
 */
class Iban {

	private $iban;

	public function __construct( string $iban ) {
		$this->iban = $iban;
	}

	public function toString(): string {
		return $this->iban;
	}

	public function accountNrFromDeIban(): string {
		return substr( $this->iban, 12 );
	}

	public function bankCodeFromDeIban(): string {
		return substr( $this->iban, 4, 8 );
	}

	public function getCountryCode() {
		return substr( $this->iban, 0, 2 );
	}

}
