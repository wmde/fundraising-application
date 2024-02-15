<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use WMDE\Fundraising\DonationContext\Domain\Model\DonorType;

/**
 * This class is for converting address type strings from the front end
 * to DonorType used by the Donation context and vice-versa.
 */
class AddressType {
	public const PERSON = 'person';
	public const COMPANY = 'company';
	public const EMAIL = 'email';
	public const ANONYMOUS = 'anonymous';
	public const LEGACY_COMPANY = 'firma';
	public const LEGACY_ANONYMOUS = 'anonym';

	public static function presentationAddressTypeToDonorType( string $presentationAddressType ): DonorType {
		return match ( $presentationAddressType ) {
			self::PERSON => DonorType::PERSON,
			self::COMPANY, self::LEGACY_COMPANY => DonorType::COMPANY,
			self::EMAIL => DonorType::EMAIL,
			self::ANONYMOUS, self::LEGACY_ANONYMOUS => DonorType::ANONYMOUS,
			default => throw new \UnexpectedValueException(
				sprintf( 'Unexpected Presentation Address Type: %s', $presentationAddressType, )
			)
		};
	}

	public static function donorTypeToPresentationAddressType( DonorType $donorType ): string {
		return match ( $donorType ) {
			DonorType::PERSON => self::PERSON,
			DonorType::COMPANY => self::LEGACY_COMPANY,
			DonorType::EMAIL => self::EMAIL,
			DonorType::ANONYMOUS => self::LEGACY_ANONYMOUS,
		};
	}
}
