<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use WMDE\Fundraising\DonationContext\Domain\Model\Donor;
use WMDE\Fundraising\DonationContext\Domain\Model\DonorType;

/**
 * This class is for generating the expected address type strings for the AddDonationRequest und UpdateDonorRequest.
 */
class AddressType {
	public const PERSON = 'person';
	public const COMPANY = 'company';
	public const EMAIL = 'email';
	public const ANONYMOUS = 'anonymous';

	public const LEGACY_PERSON = 'person';
	public const LEGACY_COMPANY = 'firma';
	public const LEGACY_EMAIL = 'email';
	public const LEGACY_ANONYMOUS = 'anonym';

	private const PRESENTATION_TO_DOMAIN = [
		self::LEGACY_PERSON => DonorType::PERSON,
		self::LEGACY_COMPANY => DonorType::COMPANY,
		self::LEGACY_EMAIL => DonorType::EMAIL,
		self::LEGACY_ANONYMOUS => DonorType::ANONYMOUS
	];

	public static function presentationAddressTypeToDomainAddressType( string $presentationAddressType ): DonorType {
		if ( !isset( self::PRESENTATION_TO_DOMAIN[$presentationAddressType] ) ) {
			throw new \UnexpectedValueException( sprintf( 'Unexpected Presentation Address Type: %s', $presentationAddressType, ) );
		}
		return self::PRESENTATION_TO_DOMAIN[$presentationAddressType];
	}

	public static function donorToPresentationAddressType( Donor $donor ): string {
		$invertedMap = array_flip( self::PRESENTATION_TO_DOMAIN );
		if ( !isset( $invertedMap[$donor->getDonorType()->name] ) ) {
			throw new \UnexpectedValueException( sprintf( 'Unexpected Donor Type: %s', $donor->getDonorType()->name, ) );
		}
		return $invertedMap[$donor->getDonorType()->name];
	}

}
