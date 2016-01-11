<?php

namespace WMDE\Fundraising\Frontend\UseCases\ValidateBankData;

use WMDE\Fundraising\Frontend\BankDataConverter;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen <kai.nissen@wikimedia.de>
 */
class ValidateBankDataUseCase {

	public function __construct( BankDataConverter $bankDataConverter ) {
		$this->bankDataConverter = $bankDataConverter;
	}

	public function checkIban( string $iban ) {
		return $this->bankDataConverter->getBankDataFromIban( $iban );
	}

	public function generateIban( string $accountNumber, string $bankCode ) {
		return $this->bankDataConverter->getBankDataFromAccountData( $accountNumber, $bankCode );
	}

}
