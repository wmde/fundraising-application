<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use WMDE\Fundraising\PaymentContext\Domain\PaymentType;

/**
 * @phpstan-type PaymentTypeDomainToggle array{donation-enabled:bool,membership-enabled:bool}
 */
class PaymentTypeConfiguration {
	/**
	 * @param array<string,PaymentTypeDomainToggle> $paymentTypeConfig
	 * @return PaymentType[]
	 */
	public static function getAllowedPaymentTypesForDonation( array $paymentTypeConfig ): array {
		return self::getAllowedPaymentTypesForKey( $paymentTypeConfig, 'donation-enabled' );
	}

	/**
	 * @param array<string,PaymentTypeDomainToggle> $paymentTypeConfig
	 * @return PaymentType[]
	 */
	public static function getAllowedPaymentTypesForMembership( array $paymentTypeConfig ): array {
		return self::getAllowedPaymentTypesForKey( $paymentTypeConfig, 'membership-enabled' );
	}

	/**
	 * @param array<string,PaymentTypeDomainToggle> $paymentTypeConfig
	 * @param string $domainKey
	 * @return PaymentType[]
	 */
	private static function getAllowedPaymentTypesForKey( array $paymentTypeConfig, string $domainKey ): array {
		$paymentTypes = [];
		foreach ( $paymentTypeConfig as $paymentTypeName => $domainToggle ) {
			if ( $domainToggle[$domainKey] ) {
				$paymentTypes[] = PaymentType::from( $paymentTypeName );
			}
		}
		return $paymentTypes;
	}

}
