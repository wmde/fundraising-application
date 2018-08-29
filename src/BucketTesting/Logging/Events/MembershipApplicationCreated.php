<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting\Logging\Events;

use WMDE\Fundraising\Frontend\BucketTesting\Logging\LoggingEvent;

class MembershipApplicationCreated implements LoggingEvent {

	private $metadata;

	public function __construct( int $membershipApplicationId ) {
		$this->metadata = [
			'id' => $membershipApplicationId
		];
	}

	public function getMetaData(): array {
		return $this->metadata;
	}

	public function getName(): string {
		return 'membershipApplicationCreated';
	}

}