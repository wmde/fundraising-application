<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\PaymentContext\UseCases\GenerateIban;

use WMDE\Fundraising\PaymentContext\Domain\BankDataConverter;
use WMDE\Fundraising\PaymentContext\Domain\KontoCheckIbanValidator;
use WMDE\Fundraising\PaymentContext\ResponseModel\IbanResponse;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen <kai.nissen@wikimedia.de>
 */
class GenerateIbanUseCase {

	private $bankDataConverter;
	private $ibanValidator;

	public function __construct( BankDataConverter $bankDataConverter, KontoCheckIbanValidator $ibanValidator ) {
		$this->bankDataConverter = $bankDataConverter;
		$this->ibanValidator = $ibanValidator;
	}

	public function generateIban( GenerateIbanRequest $request ): IbanResponse {
		try {
			$bankData = $this->bankDataConverter->getBankDataFromAccountData(
				$request->getBankAccount(),
				$request->getBankCode()
			);

			if ( $this->ibanValidator->isIbanBlocked( $bankData->getIban() ) ) {
				return IbanResponse::newFailureResponse();
			}
		}
		catch ( \RuntimeException $ex ) {
			return IbanResponse::newFailureResponse();
		}

		return IbanResponse::newSuccessResponse( $bankData );
	}

}
