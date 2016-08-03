<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\PaymentContext\UseCases\CheckIban;

use WMDE\Fundraising\Frontend\PaymentContext\Domain\BankDataConverter;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\Iban;
use WMDE\Fundraising\Frontend\PaymentContext\ResponseModel\IbanResponse;

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
