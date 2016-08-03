<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonatingContext\Infrastructure;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PayPalPaymentNotificationVerifierException extends \RuntimeException {

	public function __construct( string $message, \Exception $previous = null ) {
		parent::__construct( $message, 0, $previous );
	}

}
