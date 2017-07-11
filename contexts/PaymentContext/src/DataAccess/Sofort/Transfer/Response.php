<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\PaymentContext\DataAccess\Sofort\Transfer;

class Response {

	/**
	 * @var string
	 */
	private $transactionId = '';
	/**
	 * @var string
	 */
	private $paymentUrl = '';

	public function getTransactionId(): string {
		return $this->transactionId;
	}

	public function setTransactionId( string $transactionId ): void {
		$this->transactionId = $transactionId;
	}

	public function getPaymentUrl(): string {
		return $this->paymentUrl;
	}

	public function setPaymentUrl( string $paymentUrl ): void {
		$this->paymentUrl = $paymentUrl;
	}
}
