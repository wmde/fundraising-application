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

	private BucketLogger $bucketLogger;

	/** @var callable */
	private $getSelectedBuckets;

	private bool $consentGiven = false;

	public function __construct( BucketLogger $bucketLogger, callable $getSelectedBuckets ) {
		$this->bucketLogger = $bucketLogger;
		$this->getSelectedBuckets = $getSelectedBuckets;
	}

	public function onDonationCreated( DonationCreatedEvent $event ): void {
		if ( !$this->consentGiven ) {
			return;
		}

		$this->bucketLogger->writeEvent(
			new DonationCreated( $event->getDonationId() ),
			...\call_user_func( $this->getSelectedBuckets )
		);
	}

	public function onMembershipCreated( MembershipCreatedEvent $event ): void {
		if ( !$this->consentGiven ) {
			return;
		}

		$this->bucketLogger->writeEvent(
			new MembershipApplicationCreated( $event->getMembershipId() ),
			...\call_user_func( $this->getSelectedBuckets )
		);
	}

	public function setConsentGiven( bool $consentGiven ): void {
		$this->consentGiven = $consentGiven;
	}

	public static function getSubscribedEvents() {
		return [
			DonationCreatedEvent::class => 'onDonationCreated',
			MembershipCreatedEvent::class => 'onMembershipCreated',
		];
	}
}
