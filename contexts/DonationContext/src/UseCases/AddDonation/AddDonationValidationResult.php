<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\UseCases\AddDonation;

use WMDE\Fundraising\Frontend\Validation\ValidationResult;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddDonationValidationResult extends ValidationResult {

	public const SOURCE_PAYMENT_TYPE = 'zahlweise';
	public const SOURCE_PAYMENT_AMOUNT = 'amount';
	public const SOURCE_IBAN = 'iban';
	public const SOURCE_BIC = 'bic';
	public const SOURCE_BANK_NAME = 'bankname';
	public const SOURCE_BANK_CODE = 'blz';
	public const SOURCE_BANK_ACCOUNT = 'konto';
	public const SOURCE_DONOR_EMAIL = 'email';
	public const SOURCE_DONOR_COMPANY = 'companyName';
	public const SOURCE_DONOR_FIRST_NAME = 'firstName';
	public const SOURCE_DONOR_LAST_NAME = 'lastName';
	public const SOURCE_DONOR_SALUTATION = 'salutation';
	public const SOURCE_DONOR_TITLE = 'title';
	public const SOURCE_DONOR_STREET_ADDRESS = 'street';
	public const SOURCE_DONOR_POSTAL_CODE = 'postcode';
	public const SOURCE_DONOR_CITY = 'city';
	public const SOURCE_DONOR_COUNTRY = 'country';
	public const SOURCE_TRACKING_SOURCE = 'source';

	public const VIOLATION_TOO_LOW = 'too-low';
	public const VIOLATION_TOO_HIGH = 'too-high';
	public const VIOLATION_WRONG_LENGTH = 'wrong-length';
	public const VIOLATION_NOT_MONEY = 'not-money';
	public const VIOLATION_MISSING = 'missing';
	public const VIOLATION_IBAN_BLOCKED = 'iban-blocked';
	public const VIOLATION_IBAN_INVALID = 'iban-invalid';
	public const VIOLATION_NOT_DATE = 'not-date';
	public const VIOLATION_NOT_PHONE_NUMBER = 'not-phone';
	public const VIOLATION_NOT_EMAIL = 'not-email';
	public const VIOLATION_NOT_POSTCODE = 'not-postcode';
	public const VIOLATION_WRONG_PAYMENT_TYPE = 'invalid_payment_type';
	public const VIOLATION_TEXT_POLICY = 'text_policy';

}