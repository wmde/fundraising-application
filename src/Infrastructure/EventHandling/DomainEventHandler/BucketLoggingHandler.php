<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\EventHandling\DomainEventHandler;

use WMDE\Fundraising\DonationContext\Domain\Event\DonationCreatedEvent;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\BucketLogger;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\Events\DonationCreated;
use WMDE\Fundraising\Frontend\BucketTesting\Logging\Events\MembershipApplicationCreated;
use WMDE\Fundraising\MembershipContext\Domain\Event\MembershipCreatedEvent;

/**
 * Log the visitors bucket IDs when they donate or apply for membership
 */
class BucketLoggingHandler {

	/** @var callable */
	private $getSelectedBuckets;

	public function __construct(
		private readonly BucketLogger $bucketLogger,
		callable $getSelectedBuckets
	) {
		$this->getSelectedBuckets = $getSelectedBuckets;
	}

	public function onDonationCreated( DonationCreatedEvent $event ): void {
		$this->bucketLogger->writeEvent(
			new DonationCreated( $event->getDonationId() ),
			...\call_user_func( $this->getSelectedBuckets )
		);
	}

	public function onMembershipCreated( MembershipCreatedEvent $event ): void {
		$this->bucketLogger->writeEvent(
			new MembershipApplicationCreated( $event->getMembershipId() ),
			...\call_user_func( $this->getSelectedBuckets )
		);
	}

	/**
	 * @return array<class-string,string>
	 */
	public static function getSubscribedEvents(): array {
		return [
			DonationCreatedEvent::class => 'onDonationCreated',
			MembershipCreatedEvent::class => 'onMembershipCreated',
		];
	}
}
