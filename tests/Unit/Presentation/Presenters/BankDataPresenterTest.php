<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation\Presenters;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Presentation\Presenters\BankDataPresenter;
use WMDE\Fundraising\MembershipContext\Tests\Fixtures\ValidPayments;

#[CoversClass( BankDataPresenter::class )]
class BankDataPresenterTest extends TestCase {
	public function testItReturnsOnlyModernBankData(): void {
		$expectedBankData = [
			'iban' => ValidPayments::PAYMENT_IBAN,
			'bic' => ValidPayments::PAYMENT_BIC,
			'bankname' => ValidPayments::PAYMENT_BANK_NAME,
		];

		$result = BankDataPresenter::getBankDataArray( [
			'iban' => ValidPayments::PAYMENT_IBAN,
			'bic' => ValidPayments::PAYMENT_BIC,
			'bankname' => ValidPayments::PAYMENT_BANK_NAME,
			'amount' => 1299,
			'konto' => ValidPayments::PAYMENT_BANK_ACCOUNT,
			'blz' => ValidPayments::PAYMENT_BANK_CODE
		] );

		$this->assertSame( $expectedBankData, $result );
	}

	public function testItReturnsEmptyDefaultValues(): void {
		$expectedBankData = [
			'iban' => '',
			'bic' => '',
			'bankname' => '',
		];

		$result = BankDataPresenter::getBankDataArray( [] );

		$this->assertSame( $expectedBankData, $result );
	}
}
