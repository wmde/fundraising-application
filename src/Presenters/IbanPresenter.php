<?php

namespace WMDE\Fundraising\Frontend\Presenters;

use WMDE\Fundraising\Frontend\Domain\BankData;
use WMDE\Fundraising\Frontend\ResponseModel\IbanResponse;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class IbanPresenter {

	public function present( IbanResponse $iban ) {
		if ( $iban->isSuccessful() ) {
			return $this->newSuccessResponse( $iban->getBankData() );
		}

		return $this->newErrorResponse();
	}

	private function newSuccessResponse( BankData $bankData ) {
		return [
			'status' => 'OK',
			'bic' => $bankData->getBic(),
			'iban' => $bankData->getIban(),
			'account' => $bankData->getAccount(),
			'bankCode' => $bankData->getBankCode(),
			'bankName' => $bankData->getBankName(),
		];
	}

	private function newErrorResponse() {
		return [ 'status' => 'ERR' ];
	}

}