<?php

namespace WMDE\Fundraising\Frontend\UseCases\CheckIban;

use WMDE\Fundraising\Frontend\BankDataConverter;
use WMDE\Fundraising\Frontend\Domain\Iban;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen <kai.nissen@wikimedia.de>
 */
class CheckIbanUseCase {

	public function __construct( BankDataConverter $bankDataConverter ) {
		$this->bankDataConverter = $bankDataConverter;
	}

	public function checkIban( Iban $iban ) {
		if ( !$this->bankDataConverter->validateIban( $iban ) ) {
			return false;
		}

		return $this->bankDataConverter->getBankDataFromIban( $iban );
	}

}
