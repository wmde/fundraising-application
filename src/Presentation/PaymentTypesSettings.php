<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use InvalidArgumentException;

/**
 * Class PaymentTypesSettings
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

	public const PURPOSE_DONATION = 'donation-enabled';
	public const PURPOSE_MEMBERSHIP = 'membership-enabled';

	private $settings = [];

	public function __construct( array $settings ) {
		$this->settings = $settings;
	}

	/**
	 * @return string[]
	 */
	public function getEnabledForDonation(): array {
		return $this->getEnabledTypes( self::PURPOSE_DONATION );
	}

	/**
	 * @return string[]
	 */
	public function getEnabledForMembershipApplication(): array {
		return $this->getEnabledTypes( self::PURPOSE_MEMBERSHIP );
	}

	public function updateSetting( string $paymentType, string $purpose, bool $value ): void {
		if ( !array_key_exists( $paymentType, $this->settings ) ) {
			throw new InvalidArgumentException( "Can not update setting of unknown paymentType '$paymentType'." );
		}
		if ( !array_key_exists( $purpose, $this->settings[$paymentType] ) ) {
			throw new InvalidArgumentException( "Can not update setting of unknown purpose '$purpose'." );
		}

		$this->settings[$paymentType][$purpose] = $value;
	}

	/**
	 * @return string[]
	 */
	private function getEnabledTypes( string $purpose ): array {
		return array_keys( array_filter( $this->settings, function ( $config ) use ( $purpose ) {
			return ( $config[$purpose] ?? false ) === true;
		} ) );
	}
}

