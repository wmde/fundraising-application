<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Tests\Unit\DataAccess;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\DonationContext\DataAccess\DoctrineDonationRepository;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\BankTransferPayment;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\CreditCardPayment;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PaymentMethod;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\SofortPayment;

/**
 * @covers \WMDE\Fundraising\Frontend\DonationContext\DataAccess\DoctrineDonationRepository
 */
class DoctrineDonationRepositoryTest extends TestCase {

	/**
	 * @dataProvider getPaymentMethodsAndTransferCodes
	 */
	public function testGetBankTransferCode_identifierIsReturned( string $expectedOutput, PaymentMethod $payment ): void {
		$this->assertEquals( $expectedOutput, DoctrineDonationRepository::getBankTransferCode( $payment ) );
	}

	public function getPaymentMethodsAndTransferCodes(): array {
		return [
			[ 'ffg', new SofortPayment( 'ffg' ) ],
			[ 'hhi', new BankTransferPayment( 'hhi' ) ],
			[ '', new CreditCardPayment() ],
		];
	}
}
