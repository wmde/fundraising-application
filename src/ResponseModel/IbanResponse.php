<?php

namespace WMDE\Fundraising\Frontend\ResponseModel;

use WMDE\Fundraising\Frontend\Domain\BankData;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class IbanResponse {

	public static function newSuccessResponse( BankData $bankData ) {
		return new self( $bankData );
	}

	public static function newFailureResponse() {
		return new self();
	}

	private $bankData;

	private function __construct( BankData $bankData = null ) {
		$this->bankData = $bankData;
	}

	public function isSuccessful(): bool {
		return $this->bankData !== null;
	}

	public function getBankData(): BankData {
		if ( $this->bankData === null ) {
			throw new \RuntimeException( 'Cannot get the bank data of a failure response' );
		}

		return $this->bankData;
	}

}
