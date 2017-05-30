<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\PaymentContext\UseCases\CheckIban;

use WMDE\Fundraising\Frontend\PaymentContext\Domain\BankDataConverter;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\Iban;
use WMDE\Fundraising\Frontend\PaymentContext\ResponseModel\IbanResponse;
use WMDE\Fundraising\Frontend\Validation\IbanValidator;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen <kai.nissen@wikimedia.de>
 */
class CheckIbanUseCase {

	private $bankDataConverter;
	private $ibanValidator;

	public function __construct( BankDataConverter $bankDataConverter, IbanValidator $ibanValidator ) {
		$this->bankDataConverter = $bankDataConverter;
		$this->ibanValidator = $ibanValidator;
	}

	public function checkIban( Iban $iban ): IbanResponse {
		if ( !$this->ibanValidator->validate( $iban )->isSuccessful() ) {
			return IbanResponse::newFailureResponse();
		}

		return IbanResponse::newSuccessResponse(
			$this->bankDataConverter->getBankDataFromIban( $iban )
		);
	}

}
