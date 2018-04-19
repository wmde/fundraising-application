<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\PaymentContext\UseCases\CheckIban;

use WMDE\Fundraising\PaymentContext\Domain\BankDataGenerator;
use WMDE\Fundraising\PaymentContext\Domain\IbanValidator;
use WMDE\Fundraising\PaymentContext\Domain\Model\Iban;
use WMDE\Fundraising\PaymentContext\ResponseModel\IbanResponse;

/**
 * TODO: move to PaymentContext
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen <kai.nissen@wikimedia.de>
 */
class CheckIbanUseCase {

	private $bankDataConverter;
	private $ibanValidator;

	public function __construct( BankDataGenerator $bankDataConverter, IbanValidator $ibanValidator ) {
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
