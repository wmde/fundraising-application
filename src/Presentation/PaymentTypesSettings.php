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
 *     PaymentTypesSettings::PURPOSE_DONATION => true,
 *     PaymentTypesSettings::PURPOSE_MEMBERSHIP => true
 *   ],
 *   'UEB' => [
 *     PaymentTypesSettings::PURPOSE_DONATION => true,
 *     PaymentTypesSettings::PURPOSE_MEMBERSHIP => false
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

	public function setSettingToFalse( string $paymentType, string $settingName ): void {
		if ( !array_key_exists( $paymentType, $this->settings ) ) {
			throw new InvalidArgumentException( "Can not update setting of unknown paymentType '$paymentType'." );
		}
		if ( !array_key_exists( $settingName, $this->settings[$paymentType] ) ) {
			throw new InvalidArgumentException( "Can not update setting of unknown purpose '$settingName'." );
		}

		$this->settings[$paymentType][$settingName] = false;
	}

	/**
	 * @param string $settingName
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

