<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Logging\Events;

use WMDE\Fundraising\Frontend\BucketTesting\Logging\LoggingEvent;

class DonationCreated implements LoggingEvent {

	/**
	 * @var array<string, int|string>
	 */
	private array $metadata;

	public function __construct( int $donationId ) {
		$this->metadata = [
			'id' => $donationId
		];
	}

	/**
	 * @return array<string, int|string>
	 */
	public function getMetaData(): array {
		return $this->metadata;
	}

	public function getName(): string {
		return 'donationCreated';
	}

}
