<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * Log information on changes to the donation during its lifecycle.
 */
interface DonationEventLogger {
	public function log( int $donationId, string $message );
}