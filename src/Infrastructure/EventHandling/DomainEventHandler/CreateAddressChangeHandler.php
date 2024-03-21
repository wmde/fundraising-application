<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\EventHandling\DomainEventHandler;

use Doctrine\ORM\EntityManagerInterface;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChange;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChangeBuilder;
use WMDE\Fundraising\DonationContext\Domain\Event\DonationCreatedEvent;
use WMDE\Fundraising\DonationContext\Domain\Event\DonorUpdatedEvent;
use WMDE\Fundraising\DonationContext\Domain\Model\Donor;
use WMDE\Fundraising\DonationContext\Domain\Model\Donor\Address\NoAddress;
use WMDE\Fundraising\Frontend\Infrastructure\EventHandling\EventDispatcher;
use WMDE\Fundraising\MembershipContext\Domain\Event\MembershipCreatedEvent;

class CreateAddressChangeHandler {

	private EntityManagerInterface $entityManager;

	public function __construct( EntityManagerInterface $entityManager, EventDispatcher $dispatcher ) {
		$this->entityManager = $entityManager;
		$dispatcher->addEventListener( DonationCreatedEvent::class, [ $this, 'onDonationCreated' ] )
			->addEventListener( MembershipCreatedEvent::class, [ $this, 'onMembershipCreated' ] )
			->addEventListener( DonorUpdatedEvent::class, [ $this, 'onDonorUpdated' ] );
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
		}
		// No else here, AddressChange will throw an exception when reference type is not set

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

	public function onDonorUpdated( DonorUpdatedEvent $event ): void {
		if ( !$this->donorHasNoAddress( $event->getPreviousDonor() ) || $this->donorHasNoAddress( $event->getNewDonor() ) ) {
			return;
		}

		$addressChangeBuilder = AddressChangeBuilder::create();
		$addressChangeBuilder->forDonation( $event->getDonationId() );

		if ( $event->getNewDonor()->isPrivatePerson() ) {
			$addressChangeBuilder->forPerson();
		} elseif ( $event->getNewDonor()->isCompany() ) {
			$addressChangeBuilder->forCompany();
		}
		$this->persistIfNeeded( $addressChangeBuilder->build() );
	}

	private function donorHasNoAddress( Donor $donor ): bool {
		return in_array( get_class( $donor ), [
			Donor\EmailDonor::class,
			Donor\AnonymousDonor::class
		] );
	}

	private function persistIfNeeded( AddressChange $addressChange ): void {
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
