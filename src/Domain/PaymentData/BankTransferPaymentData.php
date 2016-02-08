<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Domain\PaymentData;

use WMDE\Fundraising\Frontend\Domain\PaymentData;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class BankTransferPaymentData extends PaymentData implements PaymentType {

	private $transferCode;

	public function getPaymentType() {
		return self::PAYMENT_TYPE_BANK_TRANSFER;
	}

	public function getTransferCode(): string {
		return $this->transferCode;
	}

	public function setTransferCode( string $transferCode = '' ) {
		if ( empty( $transferCode ) ) {
			$transferCode = $this->generateTransferCode();
		}
		$this->transferCode = $transferCode;

		return $this;
	}

	private function generateTransferCode() {
		$transferCode = 'W-Q-';

		for ( $i = 0; $i < 6; ++$i ) {
			$transferCode .= $this->getRandomCharacter();
		}
		$transferCode .= '-' . $this->getRandomCharacter();

		return $transferCode;
	}

	private function getRandomCharacter() {
		$charSet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

		return $charSet[random_int( 0, strlen( $charSet ) )];
	}

}
