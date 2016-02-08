<?php

namespace WMDE\Fundraising\Frontend\Domain\PaymentData;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
interface PaymentType {

	const PAYMENT_TYPE_BANK_TRANSFER = 'UEB';
	const PAYMENT_TYPE_CREDIT_CARD = 'MCP';
	const PAYMENT_TYPE_PAYPAL = 'PPL';
	const PAYMENT_TYPE_DIRECT_DEBIT = 'BEZ';

	public function getPaymentType();

}
