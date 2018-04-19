<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\PaymentContext\Domain;

use WMDE\Fundraising\PaymentContext\Domain\Model\BankData;
use WMDE\Fundraising\PaymentContext\Domain\Model\Iban;

/**
 * TODO: move to PaymentContext
 *
 * @licence GNU GPL v2+
 * @author Christoph Fischer < christoph.fischer@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface BankDataGenerator {

	public function getBankDataFromAccountData( string $account, string $bankCode ): BankData;

	public function getBankDataFromIban( Iban $iban ): BankData;

}
