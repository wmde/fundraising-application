<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Logging\Events;

use WMDE\Fundraising\Frontend\BucketTesting\Logging\LoggingEvent;

class MembershipApplicationCreated implements LoggingEvent {

	/**
	 * @var array<string, int|string>
	 */
	private array $metadata;

	public function __construct( int $membershipApplicationId ) {
		$this->metadata = [
			'id' => $membershipApplicationId
		];
	}

	/**
	 * @return array<string, int|string>
	 */
	public function getMetaData(): array {
		return $this->metadata;
	}

	public function getName(): string {
		return 'membershipApplicationCreated';
	}

}
