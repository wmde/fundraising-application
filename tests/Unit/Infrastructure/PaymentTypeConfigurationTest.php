<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Infrastructure\PaymentTypeConfiguration;
use WMDE\Fundraising\PaymentContext\Domain\PaymentType;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\PaymentTypeConfiguration
 */
class PaymentTypeConfigurationTest extends TestCase {
	private const PAYMENT_TYPE_CONFIG = [
		'BEZ' => [
			"donation-enabled" => true,
			"membership-enabled" => true
		],
		'UEB' => [
			"donation-enabled" => true,
			"membership-enabled" => true
		],
		'PPL' => [
			"donation-enabled" => true,
			"membership-enabled" => false
		],
	];

	public function testGetAllowedPaymentTypesForDonation(): void {
		$paymentTypes = PaymentTypeConfiguration::getAllowedPaymentTypesForDonation( self::PAYMENT_TYPE_CONFIG );

		$this->assertSame( [ PaymentType::DirectDebit, PaymentType::BankTransfer, PaymentType::Paypal ], $paymentTypes );
	}

	public function testGetAllowedPaymentTypesForMembership(): void {
		$paymentTypes = PaymentTypeConfiguration::getAllowedPaymentTypesForMembership( self::PAYMENT_TYPE_CONFIG );

		$this->assertSame( [ PaymentType::DirectDebit, PaymentType::BankTransfer ], $paymentTypes );
	}
}
