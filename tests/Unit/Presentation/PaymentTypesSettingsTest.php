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
				PaymentTypesSettings::PURPOSE_DONATION => true,
				PaymentTypesSettings::PURPOSE_MEMBERSHIP => true
			],
			'b' => [
				PaymentTypesSettings::PURPOSE_DONATION => false,
				PaymentTypesSettings::PURPOSE_MEMBERSHIP => true
			],
			'c' => [
				PaymentTypesSettings::PURPOSE_DONATION => true,
				PaymentTypesSettings::PURPOSE_MEMBERSHIP => false
			],
			'd' => [
				PaymentTypesSettings::PURPOSE_DONATION => false,
				PaymentTypesSettings::PURPOSE_MEMBERSHIP => false
			]
		] );
		$this->assertSame( [ 'a', 'c' ], $settings->getEnabledForDonation() );
	}

	public function testEnabledForMembershipApplication(): void {
		$settings = new PaymentTypesSettings( [
			'd' => [
				PaymentTypesSettings::PURPOSE_DONATION => true,
				PaymentTypesSettings::PURPOSE_MEMBERSHIP => true
			],
			'e' => [
				PaymentTypesSettings::PURPOSE_DONATION => true,
				PaymentTypesSettings::PURPOSE_MEMBERSHIP => false
			],
			'f' => [
				PaymentTypesSettings::PURPOSE_DONATION => false,
				PaymentTypesSettings::PURPOSE_MEMBERSHIP => true
			],
		] );
		$this->assertSame( [ 'd', 'f' ], $settings->getEnabledForMembershipApplication() );
	}

	public function testSettingsOnlyTrueWhenStriclySo(): void {
		$settings = new PaymentTypesSettings( [
			'a' => [
				PaymentTypesSettings::PURPOSE_DONATION => 1,
				PaymentTypesSettings::PURPOSE_MEMBERSHIP => 'yes'
			],
			'b' => [
				PaymentTypesSettings::PURPOSE_DONATION => true,
				PaymentTypesSettings::PURPOSE_MEMBERSHIP => true
			],
		] );
		$this->assertSame( [ 'b' ], $settings->getEnabledForDonation() );
		$this->assertSame( [ 'b' ], $settings->getEnabledForMembershipApplication() );
	}

	public function testDisablePurposeForOneType(): void {
		$settings = new PaymentTypesSettings( [
			'BEZ' => [
				PaymentTypesSettings::PURPOSE_DONATION => true,
				PaymentTypesSettings::PURPOSE_MEMBERSHIP => true
			],
			'UEB' => [
				PaymentTypesSettings::PURPOSE_DONATION => true,
				PaymentTypesSettings::PURPOSE_MEMBERSHIP => true
			],
			'MCP' => [
				PaymentTypesSettings::PURPOSE_DONATION => true,
				PaymentTypesSettings::PURPOSE_MEMBERSHIP => true
			],
			'PPL' => [
				PaymentTypesSettings::PURPOSE_DONATION => true,
				PaymentTypesSettings::PURPOSE_MEMBERSHIP => true
			],
			'SUB' => [
				PaymentTypesSettings::PURPOSE_DONATION => true,
				PaymentTypesSettings::PURPOSE_MEMBERSHIP => true
			]
		] );
		$settings->updateSetting( 'SUB', PaymentTypesSettings::PURPOSE_DONATION, false );
		$this->assertSame( [ 'BEZ', 'UEB', 'MCP', 'PPL' ], $settings->getEnabledForDonation() );
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Can not update setting of unknown paymentType 'IPSUM'.
	 */
	public function testUpdateSettingWithUnknownPaymentTypeThrowsException(): void {
		$settings = new PaymentTypesSettings( [
			'dolor' => [
				PaymentTypesSettings::PURPOSE_DONATION => true,
				PaymentTypesSettings::PURPOSE_MEMBERSHIP => true
			]
		] );
		$settings->updateSetting( 'IPSUM', PaymentTypesSettings::PURPOSE_DONATION, false );
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Can not update setting of unknown purpose 'foo'.
	 */
	public function testUpdateSettingWithUnknownPurposeThrowsException(): void {
		$settings = new PaymentTypesSettings( [
			'dolor' => [
				PaymentTypesSettings::PURPOSE_DONATION => true,
				PaymentTypesSettings::PURPOSE_MEMBERSHIP => true
			]
		] );
		$settings->updateSetting( 'dolor', 'foo', false );
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
