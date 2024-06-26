<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Presentation\PaymentTypesSettings;
use WMDE\Fundraising\PaymentContext\Domain\PaymentType;

#[CoversClass( PaymentTypesSettings::class )]
class PaymentTypesSettingsTest extends TestCase {

	public function testEnabledForDonation(): void {
		$settings = new PaymentTypesSettings( [
			'a' => [
				PaymentTypesSettings::ENABLE_DONATIONS => true,
				PaymentTypesSettings::ENABLE_MEMBERSHIP_APPLICATIONS => true
			],
			'b' => [
				PaymentTypesSettings::ENABLE_DONATIONS => false,
				PaymentTypesSettings::ENABLE_MEMBERSHIP_APPLICATIONS => true
			],
			'c' => [
				PaymentTypesSettings::ENABLE_DONATIONS => true,
				PaymentTypesSettings::ENABLE_MEMBERSHIP_APPLICATIONS => false
			],
			'd' => [
				PaymentTypesSettings::ENABLE_DONATIONS => false,
				PaymentTypesSettings::ENABLE_MEMBERSHIP_APPLICATIONS => false
			]
		] );
		$this->assertSame( [ 'a', 'c' ], $settings->getEnabledForDonation() );
	}

	public function testEnabledForMembershipApplication(): void {
		$settings = new PaymentTypesSettings( [
			'd' => [
				PaymentTypesSettings::ENABLE_DONATIONS => true,
				PaymentTypesSettings::ENABLE_MEMBERSHIP_APPLICATIONS => true
			],
			'e' => [
				PaymentTypesSettings::ENABLE_DONATIONS => true,
				PaymentTypesSettings::ENABLE_MEMBERSHIP_APPLICATIONS => false
			],
			'f' => [
				PaymentTypesSettings::ENABLE_DONATIONS => false,
				PaymentTypesSettings::ENABLE_MEMBERSHIP_APPLICATIONS => true
			],
		] );
		$this->assertSame( [ 'd', 'f' ], $settings->getEnabledForMembershipApplication() );
	}

	public function testGetAllowedPaymentTypesForDonation(): void {
		$settings = new PaymentTypesSettings( [
			'BEZ' => [
				PaymentTypesSettings::ENABLE_DONATIONS => true,
				PaymentTypesSettings::ENABLE_MEMBERSHIP_APPLICATIONS => true
			],
			'UEB' => [
				PaymentTypesSettings::ENABLE_DONATIONS => true,
				PaymentTypesSettings::ENABLE_MEMBERSHIP_APPLICATIONS => true
			],
			'PPL' => [
				PaymentTypesSettings::ENABLE_DONATIONS => true,
				PaymentTypesSettings::ENABLE_MEMBERSHIP_APPLICATIONS => false
			],
		] );

		$paymentTypes = $settings->getPaymentTypesForDonation();

		$this->assertSame( [ PaymentType::DirectDebit, PaymentType::BankTransfer, PaymentType::Paypal ], $paymentTypes );
	}

	public function testGetAllowedPaymentTypesForMembership(): void {
		$settings = new PaymentTypesSettings( [
			'BEZ' => [
				PaymentTypesSettings::ENABLE_DONATIONS => true,
				PaymentTypesSettings::ENABLE_MEMBERSHIP_APPLICATIONS => true
			],
			'UEB' => [
				PaymentTypesSettings::ENABLE_DONATIONS => true,
				PaymentTypesSettings::ENABLE_MEMBERSHIP_APPLICATIONS => true
			],
			'PPL' => [
				PaymentTypesSettings::ENABLE_DONATIONS => true,
				PaymentTypesSettings::ENABLE_MEMBERSHIP_APPLICATIONS => false
			],
		] );

		$paymentTypes = $settings->getPaymentTypesForMembershipApplication();

		$this->assertSame( [ PaymentType::DirectDebit, PaymentType::BankTransfer ], $paymentTypes );
	}

}
