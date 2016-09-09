<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Infrastructure;

/**
 * Logs information on changes to the donation during its lifecycle.
 *
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
interface DonationEventLogger {

	/**
	 * @param int $donationId
	 * @param string $message
	 *
	 * @throws DonationEventLogException
	 */
	public function log( int $donationId, string $message );

}