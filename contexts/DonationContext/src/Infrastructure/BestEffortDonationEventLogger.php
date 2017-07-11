<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Infrastructure;

use Psr\Log\LoggerInterface;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class BestEffortDonationEventLogger implements DonationEventLogger {

	private $donationEventLogger;
	private $logger;

	public function __construct( DonationEventLogger $donationEventLogger, LoggerInterface $logger ) {
		$this->donationEventLogger = $donationEventLogger;
		$this->logger = $logger;
	}

	public function log( int $donationId, string $message ): void {
		try {
			$this->donationEventLogger->log( $donationId, $message );
		} catch ( DonationEventLogException $e ) {
			$logContext = [
				'donationId' => $donationId,
				'exception' => $e,
				'message' => $message
			];
			$this->logger->error( 'Could not update donation event log', $logContext );
		}
	}
}
