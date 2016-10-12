<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PayPalPaymentNotificationVerifierException extends \RuntimeException {

	const ERROR_UNKNOWN = 0;
	const ERROR_UNSUPPORTED_STATUS = 1;
	const ERROR_WRONG_RECEIVER = 2;
	const ERROR_VERIFICATION_FAILED = 3;
	const ERROR_UNSUPPORTED_CURRENCY = 4;

}
