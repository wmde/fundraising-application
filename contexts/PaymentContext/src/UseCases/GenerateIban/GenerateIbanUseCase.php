<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\PaymentContext\UseCases\GenerateIban;

use WMDE\Fundraising\PaymentContext\Domain\BankDataGenerator;
use WMDE\Fundraising\PaymentContext\Domain\IbanValidator;
use WMDE\Fundraising\PaymentContext\ResponseModel\IbanResponse;

/**
 * TODO: move to PaymentContext
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen <kai.nissen@wikimedia.de>
 */
class GenerateIbanUseCase {

	private $bankDataConverter;
	private $ibanValidator;

	public function __construct( BankDataGenerator $bankDataConverter, IbanValidator $ibanValidator ) {
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
