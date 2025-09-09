<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use WMDE\Fundraising\PaymentContext\Domain\PaymentType;

/**
 * Takes a config like the following and provides read and write interface
 *
 * [
 *   'BEZ' => [
 *     PaymentTypesSettings::ENABLE_DONATIONS => true,
 *     PaymentTypesSettings::ENABLE_MEMBERSHIP_APPLICATIONS => true
 *   ],
 *   'UEB' => [
 *     PaymentTypesSettings::ENABLE_DONATIONS => true,
 *     PaymentTypesSettings::ENABLE_MEMBERSHIP_APPLICATIONS => false
 *   ]
 * ]
 */
class PaymentTypesSettings {

	public const ENABLE_DONATIONS = 'donation-enabled';
	public const ENABLE_MEMBERSHIP_APPLICATIONS = 'membership-enabled';

	/**
	 * @param array<string, array<string, bool>> $settings
	 */
	public function __construct( private readonly array $settings ) {
	}

	/**
	 * @return string[]
	 */
	public function getEnabledForDonation(): array {
		return $this->getPaymentTypesWhereSettingIsTrue( self::ENABLE_DONATIONS );
	}

	/**
	 * @return string[]
	 */
	public function getEnabledForMembershipApplication(): array {
		return $this->getPaymentTypesWhereSettingIsTrue( self::ENABLE_MEMBERSHIP_APPLICATIONS );
	}

	/**
	 * @return PaymentType[]
	 */
	public function getPaymentTypesForDonation(): array {
		return array_map(
			static fn ( string $paymentTypeName ) => PaymentType::from( $paymentTypeName ),
			$this->getEnabledForDonation()
		);
	}

	/**
	 * @return PaymentType[]
	 */
	public function getPaymentTypesForMembershipApplication(): array {
		return array_map(
			static fn ( string $paymentTypeName ) => PaymentType::from( $paymentTypeName ),
			$this->getEnabledForMembershipApplication()
		);
	}

	/**
	 * @return string[]
	 */
	private function getPaymentTypesWhereSettingIsTrue( string $settingName ): array {
		return array_keys(
			array_filter(
				$this->settings,
				static function ( $config ) use ( $settingName ) {
					return ( $config[$settingName] ?? false ) === true;
				}
			)
		);
	}
}
