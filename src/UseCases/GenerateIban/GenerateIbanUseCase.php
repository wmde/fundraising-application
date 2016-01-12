<?php

namespace WMDE\Fundraising\Frontend\UseCases\GenerateIban;

use WMDE\Fundraising\Frontend\BankDataConverter;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen <kai.nissen@wikimedia.de>
 */
class GenerateIbanUseCase {

	public function __construct( BankDataConverter $bankDataConverter ) {
		$this->bankDataConverter = $bankDataConverter;
	}

	public function generateIban( GenerateIbanRequest $request ) {
		return $this->bankDataConverter->getBankDataFromAccountData( $request->getBankAccount(), $request->getBankCode() );
	}

}
