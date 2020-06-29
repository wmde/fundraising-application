<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Payment;

/**
 * @license GPL-2.0-or-later
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
interface PaymentNotificationVerifier {

	/**
	 * Verifies the request's integrity and reassures with PayPal
	 * servers that the request was not tampered with during transfer.
	 *
	 * @param array $request
	 *
	 * @throws PayPalPaymentNotificationVerifierException
	 */
	public function verify( array $request ): void;

}
