<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChangeBuilder;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChange;
use WMDE\Fundraising\DonationContext\Domain\Model\DonorName;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineEntities\Donation;
use WMDE\Fundraising\MembershipContext\DataAccess\DoctrineEntities\MembershipApplication;

class DoctrinePostPersistSubscriberCreateAddressChange implements EventSubscriber {

	private EntityManager $entityManager;

	public function __construct( EntityManager $entityManager ) {
		$this->entityManager = $entityManager;
	}

	public function getSubscribedEvents(): array {
		return [ Events::postPersist ];
	}

	public function postPersist( LifecycleEventArgs $args ): void {
		$entity = $args->getObject();

		if ( !$entity instanceof Donation && !$entity instanceof MembershipApplication ) {
			return;
		}

		if ( $entity instanceof Donation && $entity->getDonorFullName() === 'Anonym' ) {
			return;
		}

		$addressChange = $this->createAddressChange( $entity );

		$this->entityManager->persist( $addressChange );
		$this->entityManager->flush();
	}

	/**
	 * @param Donation|MembershipApplication $entity
	 *
	 * @return AddressChange
	 */
	private function createAddressChange( object $entity ): AddressChange {
		$addressChangeBuilder = AddressChangeBuilder::create();
		$data = $entity->getDecodedData();

		if ( $entity instanceof Donation ) {
			$addressChangeBuilder->forDonation( $entity->getId() );

			if ( !isset( $data['adresstyp'] ) || $data['adresstyp'] === DonorName::PERSON_PRIVATE ) {
				$addressChangeBuilder->forPerson();
			} elseif ( $data['adresstyp'] === DonorName::PERSON_COMPANY ) {
				$addressChangeBuilder->forCompany();
			}

		} elseif ( $entity instanceof MembershipApplication ) {
			$addressChangeBuilder->forMembership( $entity->getId() );

			if ( $entity->getCompany() === null || $entity->getCompany() === '' ) {
				$addressChangeBuilder->forPerson();
			} else {
				$addressChangeBuilder->forCompany();
			}
		}

		return $addressChangeBuilder->build();
	}
}