<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation;

use WMDE\Fundraising\Frontend\Presentation\PaymentTypesSettings;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

/**
 * @covers \WMDE\Fundraising\Frontend\Presentation\PaymentTypesSettings
 */
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

	public function testSettingsOnlyTrueWhenStriclySo(): void {
		$settings = new PaymentTypesSettings( [
			'a' => [
				PaymentTypesSettings::ENABLE_DONATIONS => 1,
				PaymentTypesSettings::ENABLE_MEMBERSHIP_APPLICATIONS => 'yes'
			],
			'b' => [
				PaymentTypesSettings::ENABLE_DONATIONS => true,
				PaymentTypesSettings::ENABLE_MEMBERSHIP_APPLICATIONS => true
			],
		] );
		$this->assertSame( [ 'b' ], $settings->getEnabledForDonation() );
		$this->assertSame( [ 'b' ], $settings->getEnabledForMembershipApplication() );
	}

	public function testDisablePurposeForOneType(): void {
		$settings = new PaymentTypesSettings( [
			'BEZ' => [
				PaymentTypesSettings::ENABLE_DONATIONS => true,
				PaymentTypesSettings::ENABLE_MEMBERSHIP_APPLICATIONS => true
			],
			'UEB' => [
				PaymentTypesSettings::ENABLE_DONATIONS => true,
				PaymentTypesSettings::ENABLE_MEMBERSHIP_APPLICATIONS => true
			],
			'MCP' => [
				PaymentTypesSettings::ENABLE_DONATIONS => true,
				PaymentTypesSettings::ENABLE_MEMBERSHIP_APPLICATIONS => true
			],
			'PPL' => [
				PaymentTypesSettings::ENABLE_DONATIONS => true,
				PaymentTypesSettings::ENABLE_MEMBERSHIP_APPLICATIONS => true
			],
			'SUB' => [
				PaymentTypesSettings::ENABLE_DONATIONS => true,
				PaymentTypesSettings::ENABLE_MEMBERSHIP_APPLICATIONS => true
			]
		] );
		$settings->setSettingToFalse( 'SUB', PaymentTypesSettings::ENABLE_DONATIONS );
		$this->assertSame( [ 'BEZ', 'UEB', 'MCP', 'PPL' ], $settings->getEnabledForDonation() );
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Can not update setting of unknown paymentType 'IPSUM'.
	 */
	public function testUpdateSettingWithUnknownPaymentTypeThrowsException(): void {
		$settings = new PaymentTypesSettings( [
			'dolor' => [
				PaymentTypesSettings::ENABLE_DONATIONS => true,
				PaymentTypesSettings::ENABLE_MEMBERSHIP_APPLICATIONS => true
			]
		] );
		$settings->setSettingToFalse( 'IPSUM', PaymentTypesSettings::ENABLE_DONATIONS );
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Can not update setting of unknown purpose 'foo'.
	 */
	public function testUpdateSettingWithUnknownPurposeThrowsException(): void {
		$settings = new PaymentTypesSettings( [
			'dolor' => [
				PaymentTypesSettings::ENABLE_DONATIONS => true,
				PaymentTypesSettings::ENABLE_MEMBERSHIP_APPLICATIONS => true
			]
		] );
		$settings->setSettingToFalse( 'dolor', 'foo' );
	}

	public function testFunkySettingsNotValidatedButHarmless(): void {
		$settings = new PaymentTypesSettings( [
			'ff' => [
				'gg' => 7
			],
			2 => [
				'thing' => [ 'ignored' ]
			]
		] );
		$this->assertSame( [], $settings->getEnabledForDonation() );
		$this->assertSame( [], $settings->getEnabledForMembershipApplication() );
	}

}
