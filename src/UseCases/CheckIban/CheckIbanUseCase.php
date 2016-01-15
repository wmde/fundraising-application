<?php

namespace WMDE\Fundraising\Frontend\UseCases\CheckIban;

use WMDE\Fundraising\Frontend\BankDataConverter;
use WMDE\Fundraising\Frontend\Domain\Iban;
use WMDE\Fundraising\Frontend\ResponseModel\IbanResponse;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen <kai.nissen@wikimedia.de>
 */
class CheckIbanUseCase {

	public function __construct( BankDataConverter $bankDataConverter ) {
		$this->bankDataConverter = $bankDataConverter;
	}

	public function checkIban( Iban $iban ): IbanResponse {
		if ( !$this->bankDataConverter->validateIban( $iban ) ) {
			return IbanResponse::newFailureResponse();
		}

		return IbanResponse::newSuccessResponse(
			$this->bankDataConverter->getBankDataFromIban( $iban )
		);
	}

}
