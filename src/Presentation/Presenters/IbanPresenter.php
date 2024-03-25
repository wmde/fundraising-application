<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\PaymentContext\Domain\Model\ExtendedBankData;
use WMDE\Fundraising\PaymentContext\UseCases\BankDataFailureResponse;
use WMDE\Fundraising\PaymentContext\UseCases\BankDataSuccessResponse;

class IbanPresenter {

	/**
	 * @return array<string, string>
	 */
	public function present( BankDataSuccessResponse|BankDataFailureResponse $response ): array {
		if ( $response instanceof BankDataSuccessResponse ) {
			return $this->newSuccessResponse( $response->bankData );
		}

		return $this->newErrorResponse();
	}

	/**
	 * @return array<string, string>
	 */
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

	/**
	 * @return array<string, string>
	 */
	private function newErrorResponse(): array {
		return [ 'status' => 'ERR' ];
	}

}
