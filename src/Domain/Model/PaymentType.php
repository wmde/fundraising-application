<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Domain\Model;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface PaymentType {

	const BANK_TRANSFER = 'UEB';
	const CREDIT_CARD = 'MCP';
	const DIRECT_DEBIT = 'BEZ';
	const PAYPAL = 'PPL';
	const CASH = 'CASH';
	const BITCOIN = 'BTC';

}
