<?php

namespace WMDE\Fundraising\Frontend\UseCases\CheckIban;

use WMDE\Fundraising\Frontend\BankDataConverter;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen <kai.nissen@wikimedia.de>
 */
class CheckIbanUseCase {

	public function __construct( BankDataConverter $bankDataConverter ) {
		$this->bankDataConverter = $bankDataConverter;
	}

	public function checkIban( string $iban ) {
		return $this->bankDataConverter->getBankDataFromIban( $iban );
	}

}
