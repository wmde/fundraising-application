<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App;

use WMDE\Fundraising\Frontend\Infrastructure\UrlGenerator;

/**
 * This class contains the names of routes from routes.yaml as public constants.
 *
 * The purpose is to distinguish between hard-coded URLs and named routes and to avoid what looks like 'magic' strings
 * in the code.
 */
class Routes {

	public const CONVERT_BANKDATA = 'generate_iban';
	public const INDEX = '/';
	public const POST_COMMENT = 'api_donation_comment_post';
	public const SHOW_DONATION_CONFIRMATION = 'show_donation_confirmation';
	public const SHOW_MEMBERSHIP_CONFIRMATION = 'show_membership_confirmation';
	public const API_UPDATE_ADDRESS_PUT = 'api_address_change_put';
	public const UPDATE_ADDRESS_ALREADY_UPDATED = 'update-address-already-updated';
	public const API_UPDATE_DONOR_PUT = 'api_update_donor_put';
	public const VALIDATE_ADDRESS = 'validate-donor-address';
	public const VALIDATE_EMAIL = 'validate-email';
	public const VALIDATE_MEMBERSHIP_FEE = 'validate-fee';
	public const VALIDATE_IBAN = 'check_iban';

	/**
	 * This function generates a set of URLs for the client-side code.
	 *
	 * Many controllers pass this set of URLs to the client side as part of the "application data".
	 *
	 * @param UrlGenerator $urlGenerator
	 * @return array<string, string>
	 */
	public static function getNamedRouteUrls( UrlGenerator $urlGenerator ): array {
		return [
			'validateAddress' => $urlGenerator->generateAbsoluteUrl( self::VALIDATE_ADDRESS ),
			'validateEmail' => $urlGenerator->generateAbsoluteUrl( self::VALIDATE_EMAIL ),
			'validateIban' => $urlGenerator->generateAbsoluteUrl( self::VALIDATE_IBAN ),
			'validateMembershipFee' => $urlGenerator->generateAbsoluteUrl( self::VALIDATE_MEMBERSHIP_FEE ),
			'convertBankData' => $urlGenerator->generateAbsoluteUrl( self::CONVERT_BANKDATA ),
			'postComment' => $urlGenerator->generateAbsoluteUrl( self::POST_COMMENT ),
		];
	}
}
