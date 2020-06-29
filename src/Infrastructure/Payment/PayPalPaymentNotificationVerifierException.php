<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Payment;

/**
 * @license GPL-2.0-or-later
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PayPalPaymentNotificationVerifierException extends \RuntimeException {

	public const ERROR_UNSUPPORTED_STATUS = 1;
	public const ERROR_WRONG_RECEIVER = 2;
	public const ERROR_VERIFICATION_FAILED = 3;
	public const ERROR_UNSUPPORTED_CURRENCY = 4;

}
