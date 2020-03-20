<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\AddressChange\Domain\Model\Address;
use WMDE\Fundraising\AddressChange\Domain\Model\AddressChange;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChangeBuilder;
use WMDE\Fundraising\DonationContext\Domain\Model\DonorName;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Entities\MembershipApplication;
use WMDE\Fundraising\Frontend\Infrastructure\DoctrinePostPersistSubscriberCreateAddressChange;
use WMDE\Fundraising\Frontend\Tests\Fixtures\EntityManagerSpy;

class DoctrinePostPersistSubscriberCreateAddressChangeTest extends TestCase {

	public function testNonApplicableEntity_doesNothing(): void {
		$identifier = '77c12190-d97a-4564-9d88-160be51dd134';
		$address = Address::newCompanyAddress( 'Bank of Duckburg', 'At the end of teh road', '1234', 'Duckburg', 'DE' );
		$addressChange = AddressChangeBuilder::create( $identifier, $address )->forPerson()->forDonation( 1 )->build();

		$entityManager = new EntityManagerSpy();
		$lifeCycleEventArgs = new LifecycleEventArgs( $addressChange, $entityManager );

		$subscriber = new DoctrinePostPersistSubscriberCreateAddressChange( $entityManager );
		$subscriber->postPersist( $lifeCycleEventArgs );

		$this->assertNull( $entityManager->getEntity() );
	}

	public function testAnonymousDonor_doesNothing(): void {
		$donation = new Donation();
		$donation->setDonorFullName( 'Anonym' );

		$entityManager = new EntityManagerSpy();
		$lifeCycleEventArgs = new LifecycleEventArgs( $donation, $entityManager );

		$subscriber = new DoctrinePostPersistSubscriberCreateAddressChange( $entityManager );
		$subscriber->postPersist( $lifeCycleEventArgs );

		$this->assertNull( $entityManager->getEntity() );
	}

	public function testCreateDonation_createsAddressChange(): void {
		$donation = new Donation();
		$donation->setId( 1 );
		$donation->encodeAndSetData( [ 'adresstyp' => DonorName::PERSON_PRIVATE ] );

		$entityManager = new EntityManagerSpy();
		$lifeCycleEventArgs = new LifecycleEventArgs( $donation, $entityManager );

		$subscriber = new DoctrinePostPersistSubscriberCreateAddressChange( $entityManager );
		$subscriber->postPersist( $lifeCycleEventArgs );

		$this->assertNotNull( $entityManager->getEntity() );
		$this->assertInstanceOf( AddressChange::class, $entityManager->getEntity() );
	}

	public function testCreateDonation_hasCorrectExternalIdType(): void {
		$donation = new Donation();
		$donation->setId( 1 );
		$donation->encodeAndSetData( [ 'adresstyp' => DonorName::PERSON_PRIVATE ] );

		$entityManager = new EntityManagerSpy();
		$lifeCycleEventArgs = new LifecycleEventArgs( $donation, $entityManager );

		$subscriber = new DoctrinePostPersistSubscriberCreateAddressChange( $entityManager );
		$subscriber->postPersist( $lifeCycleEventArgs );

		$this->assertEquals(
			$entityManager->getEntity()->getExternalIdType(),
			AddressChange::EXTERNAL_ID_TYPE_DONATION
		);
	}

	public function testCreateMembership_hasCorrectExternalIdType(): void {
		$application = new MembershipApplication();
		$application->setId( 1 );
		$application->setCompany( 'ACME' );

		$entityManager = new EntityManagerSpy();
		$lifeCycleEventArgs = new LifecycleEventArgs( $application, $entityManager );

		$subscriber = new DoctrinePostPersistSubscriberCreateAddressChange( $entityManager );
		$subscriber->postPersist( $lifeCycleEventArgs );

		$this->assertEquals(
			$entityManager->getEntity()->getExternalIdType(),
			AddressChange::EXTERNAL_ID_TYPE_MEMBERSHIP
		);
	}

	public function testCreatePersonalFromDonation_hasCorrectAddressType(): void {
		$donation = new Donation();
		$donation->setId( 1 );
		$donation->encodeAndSetData( [ 'adresstyp' => DonorName::PERSON_PRIVATE ] );

		$entityManager = new EntityManagerSpy();
		$lifeCycleEventArgs = new LifecycleEventArgs( $donation, $entityManager );

		$subscriber = new DoctrinePostPersistSubscriberCreateAddressChange( $entityManager );
		$subscriber->postPersist( $lifeCycleEventArgs );

		$this->assertTrue( $entityManager->getEntity()->isPersonalAddress() );
	}

	public function testCreateFromDonationWithNoAdressyp_defaultsToPrivate(): void {
		$donation = new Donation();
		$donation->setId( 1 );

		$entityManager = new EntityManagerSpy();
		$lifeCycleEventArgs = new LifecycleEventArgs( $donation, $entityManager );

		$subscriber = new DoctrinePostPersistSubscriberCreateAddressChange( $entityManager );
		$subscriber->postPersist( $lifeCycleEventArgs );

		$this->assertTrue( $entityManager->getEntity()->isPersonalAddress() );
	}

	public function testCreateCompanyFromDonation_hasCorrectAddressType(): void {
		$donation = new Donation();
		$donation->setId( 1 );
		$donation->encodeAndSetData( [ 'adresstyp' => DonorName::PERSON_COMPANY ] );

		$entityManager = new EntityManagerSpy();
		$lifeCycleEventArgs = new LifecycleEventArgs( $donation, $entityManager );

		$subscriber = new DoctrinePostPersistSubscriberCreateAddressChange( $entityManager );
		$subscriber->postPersist( $lifeCycleEventArgs );

		$this->assertTrue( $entityManager->getEntity()->isCompanyAddress() );
	}

	public function testCreatePersonalFromMembership_hasCorrectAddressType(): void {
		$application = new MembershipApplication();
		$application->setId( 1 );

		$entityManager = new EntityManagerSpy();
		$lifeCycleEventArgs = new LifecycleEventArgs( $application, $entityManager );

		$subscriber = new DoctrinePostPersistSubscriberCreateAddressChange( $entityManager );
		$subscriber->postPersist( $lifeCycleEventArgs );

		$this->assertTrue( $entityManager->getEntity()->isPersonalAddress() );
	}

	public function testCreateCompanyFromMembership_hasCorrectAddressType(): void {
		$application = new MembershipApplication();
		$application->setId( 1 );
		$application->setCompany( 'ACME' );

		$entityManager = new EntityManagerSpy();
		$lifeCycleEventArgs = new LifecycleEventArgs( $application, $entityManager );

		$subscriber = new DoctrinePostPersistSubscriberCreateAddressChange( $entityManager );
		$subscriber->postPersist( $lifeCycleEventArgs );

		$this->assertTrue( $entityManager->getEntity()->isCompanyAddress() );
	}
}