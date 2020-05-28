<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use WMDE\EmailAddress\EmailAddress;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChange;
use WMDE\Fundraising\DonationContext\Domain\Event\DonationCreatedEvent;
use WMDE\Fundraising\DonationContext\Domain\Model\Donor;
use WMDE\Fundraising\DonationContext\Domain\Model\DonorAddress;
use WMDE\Fundraising\DonationContext\Domain\Model\DonorName;
use WMDE\Fundraising\Frontend\Infrastructure\CreateAddressChangeHandler;
use WMDE\Fundraising\Frontend\Infrastructure\EventHandling\EventDispatcher;
use WMDE\Fundraising\Frontend\Tests\Fixtures\EntityManagerSpy;
use WMDE\Fundraising\MembershipContext\Domain\Event\MembershipCreatedEvent;
use WMDE\Fundraising\MembershipContext\Domain\Model\Applicant;
use WMDE\Fundraising\MembershipContext\Domain\Model\ApplicantAddress;
use WMDE\Fundraising\MembershipContext\Domain\Model\ApplicantName;
use WMDE\Fundraising\MembershipContext\Domain\Model\PhoneNumber;

class CreateAddressChangeHandlerTest extends TestCase {

	private const DONATION_ID = 23;
	private const MEMBERSHIP_ID = 42;

	public function testAnonymousDonor_doesNothing(): void {
		$dispatcher = $this->createMock( EventDispatcher::class );
		$entityManager = new EntityManagerSpy();

		$handler = new CreateAddressChangeHandler( $entityManager, $dispatcher );
		$handler->onDonationCreated( new DonationCreatedEvent( self::DONATION_ID, null ) );

		$this->assertNull( $entityManager->getEntity() );
	}

	public function testCreateDonationForPrivatePerson_createsAddressChange(): void {
		$dispatcher = $this->createMock( EventDispatcher::class );
		$entityManager = $this->createMock( EntityManager::class );

		$entityManager->expects( $this->once() )
			->method( 'persist' )
			->with( $this->callback( function ( AddressChange $addressChange ) {
			return $addressChange->getExternalId() === self::DONATION_ID &&
				$addressChange->getExternalIdType() === AddressChange::EXTERNAL_ID_TYPE_DONATION &&
				$addressChange->isPersonalAddress();
		 }));

		$handler = new CreateAddressChangeHandler( $entityManager, $dispatcher );
		$handler->onDonationCreated( new DonationCreatedEvent( self::DONATION_ID, new Donor(
			DonorName::newPrivatePersonName(),
			new DonorAddress(),
			'nobody@nowhere.com'
		) ) );
	}

	public function testCreateDonationForCompany_createsAddressChange(): void {
		$dispatcher = $this->createMock( EventDispatcher::class );
		$entityManager = $this->createMock( EntityManager::class );

		$entityManager->expects( $this->once() )
			->method( 'persist' )
			->with( $this->callback( function ( AddressChange $addressChange ) {
				return $addressChange->getExternalId() === self::DONATION_ID &&
					$addressChange->getExternalIdType() === AddressChange::EXTERNAL_ID_TYPE_DONATION &&
					$addressChange->isCompanyAddress();
			}));

		$handler = new CreateAddressChangeHandler( $entityManager, $dispatcher );
		$handler->onDonationCreated( new DonationCreatedEvent( self::DONATION_ID, new Donor(
			DonorName::newCompanyName(),
			new DonorAddress(),
			'nobody@nowhere.com'
		) ) );
	}

	public function testCreateMembershipForPrivatePerson_createsAddressChange(): void {
		$dispatcher = $this->createMock( EventDispatcher::class );
		$entityManager = $this->createMock( EntityManager::class );

		$entityManager->expects( $this->once() )
			->method( 'persist' )
			->with( $this->callback( function ( AddressChange $addressChange ) {
				return $addressChange->getExternalId() === self::MEMBERSHIP_ID &&
					$addressChange->getExternalIdType() === AddressChange::EXTERNAL_ID_TYPE_MEMBERSHIP &&
					$addressChange->isPersonalAddress();
			}));

		$handler = new CreateAddressChangeHandler( $entityManager, $dispatcher );
		$handler->onMembershipCreated( new MembershipCreatedEvent( self::MEMBERSHIP_ID, new Applicant(
			ApplicantName::newPrivatePersonName(),
			new ApplicantAddress(),
			new EmailAddress( 'nobody@nowhere.org' ),
			new PhoneNumber( '' )
		) ) );
	}

	public function testCreateMembershipForCompany_createsAddressChange(): void {
		$dispatcher = $this->createMock( EventDispatcher::class );
		$entityManager = $this->createMock( EntityManager::class );

		$entityManager->expects( $this->once() )
			->method( 'persist' )
			->with( $this->callback( function ( AddressChange $addressChange ) {
				return $addressChange->getExternalId() === self::MEMBERSHIP_ID &&
					$addressChange->getExternalIdType() === AddressChange::EXTERNAL_ID_TYPE_MEMBERSHIP &&
					$addressChange->isCompanyAddress();
			}));

		$handler = new CreateAddressChangeHandler( $entityManager, $dispatcher );
		$handler->onMembershipCreated( new MembershipCreatedEvent( self::MEMBERSHIP_ID, new Applicant(
			ApplicantName::newCompanyName(),
			new ApplicantAddress(),
			new EmailAddress( 'nobody@nowhere.org' ),
			new PhoneNumber( '' )
		) ) );
	}
}
