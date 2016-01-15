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

	public function generateIban( GenerateIbanRequest $request ): GenerateIbanResponse {
		try {
			$bankData = $this->bankDataConverter->getBankDataFromAccountData(
				$request->getBankAccount(),
				$request->getBankCode()
			);
		}
		catch ( \RuntimeException $ex ) {
			return GenerateIbanResponse::newFailureResponse();
		}

		return GenerateIbanResponse::newSuccessResponse( $bankData );
	}

}
