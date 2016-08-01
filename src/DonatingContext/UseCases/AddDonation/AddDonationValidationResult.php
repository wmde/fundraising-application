<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonatingContext\UseCases\AddDonation;

use WMDE\Fundraising\Frontend\Validation\ValidationResult;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddDonationValidationResult extends ValidationResult {

	const SOURCE_PAYMENT_TYPE = 'zahlweise';
	const SOURCE_PAYMENT_AMOUNT = 'amount';
	const SOURCE_IBAN = 'iban';
	const SOURCE_BIC = 'bic';
	const SOURCE_BANK_NAME = 'bankname';
	const SOURCE_BANK_CODE = 'blz';
	const SOURCE_BANK_ACCOUNT = 'konto';
	const SOURCE_DONOR_EMAIL = 'email';
	const SOURCE_DONOR_COMPANY = 'companyName';
	const SOURCE_DONOR_FIRST_NAME = 'firstName';
	const SOURCE_DONOR_LAST_NAME = 'lastName';
	const SOURCE_DONOR_SALUTATION = 'salutation';
	const SOURCE_DONOR_TITLE = 'title';
	const SOURCE_DONOR_STREET_ADDRESS = 'street';
	const SOURCE_DONOR_POSTAL_CODE = 'postcode';
	const SOURCE_DONOR_CITY = 'city';
	const SOURCE_DONOR_COUNTRY = 'country';

	const VIOLATION_TOO_LOW = 'too-low';
	const VIOLATION_TOO_HIGH = 'too-high';
	const VIOLATION_WRONG_LENGTH = 'wrong-length';
	const VIOLATION_NOT_MONEY = 'not-money';
	const VIOLATION_MISSING = 'missing';
	const VIOLATION_IBAN_BLOCKED = 'iban-blocked';
	const VIOLATION_IBAN_INVALID = 'iban-invalid';
	const VIOLATION_NOT_DATE = 'not-date';
	const VIOLATION_NOT_PHONE_NUMBER = 'not-phone';
	const VIOLATION_NOT_EMAIL = 'not-email';
	const VIOLATION_NOT_POSTCODE = 'not-postcode';
	const VIOLATION_WRONG_PAYMENT_TYPE = 'invalid_payment_type';
	const VIOLATION_TEXT_POLICY = 'text_policy';

}