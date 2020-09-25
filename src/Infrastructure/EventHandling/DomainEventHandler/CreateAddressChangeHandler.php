<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\EventHandling\DomainEventHandler;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChange;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChangeBuilder;
use WMDE\Fundraising\DonationContext\Domain\Event\DonationCreatedEvent;
use WMDE\Fundraising\DonationContext\Domain\Model\Donor\Address\NoAddress;
use WMDE\Fundraising\Frontend\Infrastructure\EventHandling\EventDispatcher;
use WMDE\Fundraising\MembershipContext\Domain\Event\MembershipCreatedEvent;

class CreateAddressChangeHandler {

	private EntityManager $entityManager;

	public function __construct( EntityManager $entityManager, EventDispatcher $dispatcher ) {
		$this->entityManager = $entityManager;
		$dispatcher->addEventListener( DonationCreatedEvent::class, [ $this, 'onDonationCreated' ] )
			->addEventListener( MembershipCreatedEvent::class, [ $this, 'onMembershipCreated' ] );
	}

	public function onDonationCreated( DonationCreatedEvent $event ): void {
		// We don't need address change records for donations without address
		// In case of email-only donations, the data warehousing software that receives the exports
		// will merge them with existing donations bearing the same name and email address,
		// assigning them the address change token of the existing donation with address
		if ( $event->getDonor()->getPhysicalAddress() instanceof NoAddress ) {
			return;
		}
		$addressChangeBuilder = AddressChangeBuilder::create();
		$addressChangeBuilder->forDonation( $event->getDonationId() );

		if ( $event->getDonor()->isPrivatePerson() ) {
			$addressChangeBuilder->forPerson();
		} elseif ( $event->getDonor()->isCompany() ) {
			$addressChangeBuilder->forCompany();
		} else {
			// Preparation for https://phabricator.wikimedia.org/T220367
			return;
		}

		$this->persistIfNeeded( $addressChangeBuilder->build() );
	}

	public function onMembershipCreated( MembershipCreatedEvent $event ): void {
		$addressChangeBuilder = AddressChangeBuilder::create();
		$addressChangeBuilder->forMembership( $event->getMembershipId() );

		if ( $event->getApplicant()->isPrivatePerson() ) {
			$addressChangeBuilder->forPerson();
		} else {
			$addressChangeBuilder->forCompany();
		}
		$this->persistIfNeeded( $addressChangeBuilder->build() );
	}

	private function persistIfNeeded( AddressChange $addressChange ) {
		$countCriteria = [ 'externalId' => $addressChange->getExternalId(), 'externalIdType' => $addressChange->getExternalIdType() ];
		if ( $this->entityManager->getRepository( AddressChange::class )->count( $countCriteria ) > 0 ) {
			// New donations/memberships should never have address change records, but if they do, let's do nothing
			// Belt-and-suspenders approach for https://phabricator.wikimedia.org/T253658
			return;
		}
		$this->entityManager->persist( $addressChange );
		$this->entityManager->flush();
	}
}
