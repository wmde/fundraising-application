<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonatingContext\Infrastructure;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
interface PaymentNotificationVerifier {

	/**
	 * @param array $request
	 * @throws PayPalPaymentNotificationVerifierException
	 */
	public function verify( array $request );

}
