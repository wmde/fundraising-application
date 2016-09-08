<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonatingContext\Tests\Fixtures;

use WMDE\Fundraising\Frontend\DonatingContext\Infrastructure\DonationEventLogger;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class DonationEventLoggerSpy implements DonationEventLogger {

	private $logCalls = [];

	public function log( int $donationId, string $message ) {
		$this->logCalls[] = [ $donationId, $message ];
	}

	public function getLogCalls() {
		return $this->logCalls;
	}

}