<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure\EventHandling\DomainEventHandler;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChange;
use WMDE\Fundraising\DonationContext\Domain\Event\DonationCreatedEvent;
use WMDE\Fundraising\DonationContext\Domain\Event\DonorUpdatedEvent;
use WMDE\Fundraising\DonationContext\Domain\Model\Donor\AnonymousDonor;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Infrastructure\EventHandling\DomainEventHandler\CreateAddressChangeHandler;
use WMDE\Fundraising\Frontend\Infrastructure\EventHandling\EventDispatcher;
use WMDE\Fundraising\Frontend\Tests\Fixtures\EntityManagerSpy;
use WMDE\Fundraising\Frontend\Tests\Fixtures\EventDispatcherSpy;
use WMDE\Fundraising\MembershipContext\Domain\Event\MembershipCreatedEvent;
use WMDE\Fundraising\MembershipContext\Domain\Model\Applicant;
use WMDE\Fundraising\MembershipContext\Domain\Model\ApplicantAddress;
use WMDE\Fundraising\MembershipContext\Domain\Model\ApplicantName;
use WMDE\Fundraising\MembershipContext\Domain\Model\PhoneNumber;

#[CoversClass( CreateAddressChangeHandler::class )]
class CreateAddressChangeHandlerTest extends TestCase {

	private const DONATION_ID = 23;
	private const MEMBERSHIP_ID = 42;

	public function testConstructorInitializesEventListeners(): void {
		$dispatcher = new EventDispatcherSpy();
		$entityManager = $this->createStub( EntityManager::class );

		new CreateAddressChangeHandler( $entityManager, $dispatcher );

		$this->assertEquals(
			[ DonationCreatedEvent::class, MembershipCreatedEvent::class, DonorUpdatedEvent::class ],
			$dispatcher->getObservedEventClassNames()
		);
	}

	public function testAnonymousDonor_doesNothing(): void {
		$dispatcher = $this->createStub( EventDispatcher::class );
		$entityManager = new EntityManagerSpy( $this->createStub( EntityManagerInterface::class ) );

		$handler = new CreateAddressChangeHandler( $entityManager, $dispatcher );
		$handler->onDonationCreated( new DonationCreatedEvent( self::DONATION_ID, new AnonymousDonor() ) );

		$this->assertNull( $entityManager->getEntity() );
	}

	public function testCreateDonationForPrivatePerson_createsAddressChange(): void {
		$dispatcher = $this->createStub( EventDispatcher::class );
		$entityManager = $this->newMockEntityManager();

		$entityManager->expects( $this->once() )
			->method( 'persist' )
			->with( $this->callback( static function ( AddressChange $addressChange ) {
				return $addressChange->getExternalId() === self::DONATION_ID &&
				$addressChange->getExternalIdType() === AddressChange::EXTERNAL_ID_TYPE_DONATION &&
				$addressChange->isPersonalAddress();
			} ) );

		$handler = new CreateAddressChangeHandler( $entityManager, $dispatcher );
		$handler->onDonationCreated( new DonationCreatedEvent( self::DONATION_ID, ValidDonation::newDonor() ) );
	}

	public function testCreateDonationForCompany_createsAddressChange(): void {
		$dispatcher = $this->createStub( EventDispatcher::class );
		$entityManager = $this->newMockEntityManager();

		$entityManager->expects( $this->once() )
			->method( 'persist' )
			->with( $this->callback( static function ( AddressChange $addressChange ) {
				return $addressChange->getExternalId() === self::DONATION_ID &&
					$addressChange->getExternalIdType() === AddressChange::EXTERNAL_ID_TYPE_DONATION &&
					$addressChange->isCompanyAddress();
			} ) );

		$handler = new CreateAddressChangeHandler( $entityManager, $dispatcher );
		$handler->onDonationCreated( new DonationCreatedEvent( self::DONATION_ID, ValidDonation::newCompanyDonor() ) );
	}

	public function testCreateMembershipForPrivatePerson_createsAddressChange(): void {
		$dispatcher = $this->createStub( EventDispatcher::class );
		$entityManager = $this->newMockEntityManager();

		$entityManager->expects( $this->once() )
			->method( 'persist' )
			->with( $this->callback( static function ( AddressChange $addressChange ) {
				return $addressChange->getExternalId() === self::MEMBERSHIP_ID &&
					$addressChange->getExternalIdType() === AddressChange::EXTERNAL_ID_TYPE_MEMBERSHIP &&
					$addressChange->isPersonalAddress();
			} ) );

		$handler = new CreateAddressChangeHandler( $entityManager, $dispatcher );
		$handler->onMembershipCreated( new MembershipCreatedEvent( self::MEMBERSHIP_ID, new Applicant(
			ApplicantName::newPrivatePersonName(),
			new ApplicantAddress(),
			new EmailAddress( 'nobody@nowhere.org' ),
			new PhoneNumber( '' )
		) ) );
	}

	public function testCreateMembershipForCompany_createsAddressChange(): void {
		$dispatcher = $this->createStub( EventDispatcher::class );
		$entityManager = $this->newMockEntityManager();

		$entityManager->expects( $this->once() )
			->method( 'persist' )
			->with( $this->callback( static function ( AddressChange $addressChange ) {
				return $addressChange->getExternalId() === self::MEMBERSHIP_ID &&
					$addressChange->getExternalIdType() === AddressChange::EXTERNAL_ID_TYPE_MEMBERSHIP &&
					$addressChange->isCompanyAddress();
			} ) );

		$handler = new CreateAddressChangeHandler( $entityManager, $dispatcher );
		$handler->onMembershipCreated( new MembershipCreatedEvent( self::MEMBERSHIP_ID, new Applicant(
			ApplicantName::newCompanyName(),
			new ApplicantAddress(),
			new EmailAddress( 'nobody@nowhere.org' ),
			new PhoneNumber( '' )
		) ) );
	}

	public function testGivenAddressChangeAlreadyExists_noAddressChangeIsCreated(): void {
		$dispatcher = $this->createStub( EventDispatcher::class );
		$addressChangeRepo = $this->createMock( EntityRepository::class );
		$addressChangeRepo->expects( $this->once() )
			->method( 'count' )
			->with( $this->equalTo( [ 'externalId' => self::DONATION_ID, 'externalIdType' => AddressChange::EXTERNAL_ID_TYPE_DONATION ] ) )
			->willReturn( 1 );
		$entityManager = $this->createMock( EntityManager::class );
		$entityManager->method( 'getRepository' )->willReturn( $addressChangeRepo );

		$entityManager->expects( $this->never() )
			->method( 'persist' );

		$handler = new CreateAddressChangeHandler( $entityManager, $dispatcher );
		$handler->onDonationCreated( new DonationCreatedEvent( self::DONATION_ID, ValidDonation::newDonor() ) );
	}

	public function testUpdateDonor_createsAddressChange(): void {
		$dispatcher = $this->createStub( EventDispatcher::class );
		$entityManager = $this->newMockEntityManager();

		$entityManager->expects( $this->once() )
			->method( 'persist' )
			->with( $this->callback( static function ( AddressChange $addressChange ) {
				return $addressChange->getExternalId() === self::DONATION_ID &&
					$addressChange->getExternalIdType() === AddressChange::EXTERNAL_ID_TYPE_DONATION &&
					$addressChange->isPersonalAddress();
			} ) );

		$handler = new CreateAddressChangeHandler( $entityManager, $dispatcher );
		$handler->onDonorUpdated( new DonorUpdatedEvent(
			self::DONATION_ID,
			new AnonymousDonor(),
			ValidDonation::newDonor()
		) );
	}

	public function testUpdateDonor_andAddressChangeAlreadyExists_noAddressChangeIsCreated(): void {
		$dispatcher = $this->createStub( EventDispatcher::class );
		$addressChangeRepo = $this->createMock( EntityRepository::class );
		$addressChangeRepo->expects( $this->once() )
			->method( 'count' )
			->with( $this->equalTo( [ 'externalId' => self::DONATION_ID, 'externalIdType' => AddressChange::EXTERNAL_ID_TYPE_DONATION ] ) )
			->willReturn( 1 );
		$entityManager = $this->createMock( EntityManager::class );
		$entityManager->method( 'getRepository' )->willReturn( $addressChangeRepo );

		$entityManager->expects( $this->never() )
			->method( 'persist' );

		$handler = new CreateAddressChangeHandler( $entityManager, $dispatcher );
		$handler->onDonorUpdated( new DonorUpdatedEvent(
			self::DONATION_ID,
			new AnonymousDonor(),
			ValidDonation::newDonor()
		) );
	}

	public function testUpdateDonor_andPreviousHasAddress_doesNothing(): void {
		$dispatcher = $this->createStub( EventDispatcher::class );
		$entityManager = new EntityManagerSpy( $this->createStub( EntityManagerInterface::class ) );

		$handler = new CreateAddressChangeHandler( $entityManager, $dispatcher );
		$handler->onDonorUpdated( new DonorUpdatedEvent(
			self::DONATION_ID,
			ValidDonation::newDonor(),
			ValidDonation::newDonor(),
		) );

		$this->assertNull( $entityManager->getEntity() );
	}

	public function testUpdateDonor_andNewDoesNotHaveAddress_doesNothing(): void {
		$dispatcher = $this->createStub( EventDispatcher::class );
		$entityManager = new EntityManagerSpy( $this->createStub( EntityManagerInterface::class ) );

		$handler = new CreateAddressChangeHandler( $entityManager, $dispatcher );
		$handler->onDonorUpdated( new DonorUpdatedEvent(
			self::DONATION_ID,
			new AnonymousDonor(),
			new AnonymousDonor()
		) );

		$this->assertNull( $entityManager->getEntity() );
	}

	/**
	 * @return EntityManager&MockObject
	 */
	private function newMockEntityManager(): EntityManager {
		$addressChangeRepo = $this->createMock( EntityRepository::class );
		$addressChangeRepo->expects( $this->once() )
			->method( 'count' )
			->willReturn( 0 );
		$entityManager = $this->createMock( EntityManager::class );
		$entityManager->method( 'getRepository' )->willReturn( $addressChangeRepo );
		return $entityManager;
	}
}
