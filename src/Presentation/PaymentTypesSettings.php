<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use InvalidArgumentException;

/**
 * Class PaymentTypesSettings
 *
 * @package WMDE\Fundraising\Frontend\Presentation
 *
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

	private $settings = [];

	public function __construct( array $settings ) {
		$this->settings = $settings;
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
	 * @return string[]
	 */
	private function getPaymentTypesWhereSettingIsTrue( string $settingName ): array {
		return array_keys(
			array_filter(
				$this->settings,
				function ( $config ) use ( $settingName ) {
					return ( $config[$settingName] ?? false ) === true;
				}
			)
		);
	}
}

