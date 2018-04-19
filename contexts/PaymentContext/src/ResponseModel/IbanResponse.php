<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\PaymentContext\ResponseModel;

use WMDE\Fundraising\PaymentContext\Domain\Model\BankData;

/**
 * TODO: move to PaymentContext
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class IbanResponse {

	public static function newSuccessResponse( BankData $bankData ): self {
		return new self( $bankData );
	}

	public static function newFailureResponse(): self {
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
