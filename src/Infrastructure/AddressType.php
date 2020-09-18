<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use WMDE\Fundraising\DonationContext\Domain\Model\Donor;

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
		self::LEGACY_PERSON => self::PERSON,
		self::LEGACY_COMPANY => self::COMPANY,
		self::LEGACY_EMAIL => self::EMAIL,
		self::LEGACY_ANONYMOUS => self::ANONYMOUS
	];

	public static function presentationAddressTypeToDomainAddressType( string $presentationAddressType ): string {
		if ( !isset( self::PRESENTATION_TO_DOMAIN[$presentationAddressType] ) ) {
			throw new \UnexpectedValueException( sprintf( 'Unexpected Presentation Address Type: %s', $presentationAddressType, ) );
		}
		return self::PRESENTATION_TO_DOMAIN[$presentationAddressType];
	}

	public static function donorToPresentationAddressType( Donor $donor ): string {
		$invertedMap = array_flip( self::PRESENTATION_TO_DOMAIN );
		if ( !isset( $invertedMap[$donor->getDonorType()] ) ) {
			throw new \UnexpectedValueException( sprintf( 'Unexpected Donor Type: %s', $donor->getDonorType(), ) );
		}
		return $invertedMap[$donor->getDonorType()];
	}

}
