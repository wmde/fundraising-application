<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\PaymentContext\Domain\Model\ExtendedBankData;
use WMDE\Fundraising\PaymentContext\UseCases\BankDataFailureResponse;
use WMDE\Fundraising\PaymentContext\UseCases\BankDataSuccessResponse;

class IbanPresenter {

	public function present( BankDataSuccessResponse|BankDataFailureResponse $response ): array {
		if ( $response instanceof BankDataSuccessResponse ) {
			return $this->newSuccessResponse( $response->bankData );
		}

		return $this->newErrorResponse();
	}

	private function newSuccessResponse( ExtendedBankData $bankData ): array {
		return array_filter( [
			'status' => 'OK',
			'bic' => $bankData->bic,
			'iban' => $bankData->iban->toString(),
			'account' => $bankData->account,
			'bankCode' => $bankData->bankCode,
			'bankName' => $bankData->bankName,
		] );
	}

	private function newErrorResponse(): array {
		return [ 'status' => 'ERR' ];
	}

}
