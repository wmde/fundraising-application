<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\PaymentContext\Domain\Model;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class CreditCardPayment implements PaymentMethod {

	private $creditCardData;

	public function __construct( CreditCardTransactionData $creditCardData = null ) {
		$this->creditCardData = $creditCardData;
	}

	public function getType(): string {
		return PaymentType::CREDIT_CARD;
	}

	/**
	 * @return CreditCardTransactionData|null
	 */
	public function getCreditCardData() {
		return $this->creditCardData;
	}

	public function addCreditCardTransactionData( CreditCardTransactionData $creditCardData ) {
		$this->creditCardData = $creditCardData;
	}
}