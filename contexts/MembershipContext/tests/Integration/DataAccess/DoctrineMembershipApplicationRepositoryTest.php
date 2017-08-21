<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\Tests\Integration\DataAccess;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Entities\MembershipApplication as DoctrineApplication;
use WMDE\Fundraising\Frontend\MembershipContext\DataAccess\DoctrineApplicationRepository;
use WMDE\Fundraising\Frontend\Infrastructure\EmailAddress;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Repositories\ApplicationRepository;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Repositories\GetMembershipApplicationException;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Repositories\StoreMembershipApplicationException;
use WMDE\Fundraising\Frontend\MembershipContext\Tests\Data\ValidMembershipApplication;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ThrowingEntityManager;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @covers \WMDE\Fundraising\Frontend\MembershipContext\DataAccess\DoctrineApplicationRepository
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineMembershipApplicationRepositoryTest extends \PHPUnit\Framework\TestCase {

	const MEMBERSHIP_APPLICATION_ID = 1;
	const ID_OF_APPLICATION_NOT_IN_DB = 35505;

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	public function setUp(): void {
		$factory = TestEnvironment::newInstance()->getFactory();
		$factory->disableDoctrineSubscribers();
		$this->entityManager = $factory->getEntityManager();
		parent::setUp();
	}

	public function testValidMembershipApplicationGetPersisted(): void {
		$this->newRepository()->storeApplication( ValidMembershipApplication::newDomainEntity() );

		$expectedDoctrineEntity = ValidMembershipApplication::newDoctrineEntity();
		$expectedDoctrineEntity->setId( self::MEMBERSHIP_APPLICATION_ID );

		$this->assertDoctrineEntityIsInDatabase( $expectedDoctrineEntity );
	}

	private function newRepository(): ApplicationRepository {
		return new DoctrineApplicationRepository( $this->entityManager );
	}

	private function assertDoctrineEntityIsInDatabase( DoctrineApplication $expected ): void {
		$actual = $this->getApplicationFromDatabase( $expected->getId() );

		$this->assertNotNull( $actual->getCreationTime() );
		$actual->setCreationTime( null );

		$this->assertEquals( $expected->getDecodedData(), $actual->getDecodedData() );

		$this->assertEquals( $expected, $actual );
	}

	private function getApplicationFromDatabase( int $id ): DoctrineApplication {
		$applicationRepo = $this->entityManager->getRepository( DoctrineApplication::class );
		$donation = $applicationRepo->find( $id );
		$this->assertInstanceOf( DoctrineApplication::class, $donation );
		return $donation;
	}

	public function testIdGetsAssigned(): void {
		$application = ValidMembershipApplication::newDomainEntity();

		$this->newRepository()->storeApplication( $application );

		$this->assertSame( self::MEMBERSHIP_APPLICATION_ID, $application->getId() );
	}

	public function testWhenPersistenceFails_domainExceptionIsThrown(): void {
		$donation = ValidMembershipApplication::newDomainEntity();

		$repository = new DoctrineApplicationRepository( ThrowingEntityManager::newInstance( $this ) );

		$this->expectException( StoreMembershipApplicationException::class );
		$repository->storeApplication( $donation );
	}

	public function testWhenMembershipApplicationInDatabase_itIsReturnedAsMatchingDomainEntity(): void {
		$this->storeDoctrineApplication( ValidMembershipApplication::newDoctrineEntity() );

		$expected = ValidMembershipApplication::newAutoConfirmedDomainEntity();
		$expected->assignId( self::MEMBERSHIP_APPLICATION_ID );

		$this->assertEquals(
			$expected,
			$this->newRepository()->getApplicationById( self::MEMBERSHIP_APPLICATION_ID )
		);
	}

	private function storeDoctrineApplication( DoctrineApplication $application ): void {
		$this->entityManager->persist( $application );
		$this->entityManager->flush();
	}

	public function testWhenEntityDoesNotExist_getEntityReturnsNull(): void {
		$this->assertNull( $this->newRepository()->getApplicationById( self::ID_OF_APPLICATION_NOT_IN_DB ) );
	}

	public function testWhenReadFails_domainExceptionIsThrown(): void {
		$repository = new DoctrineApplicationRepository( ThrowingEntityManager::newInstance( $this ) );

		$this->expectException( GetMembershipApplicationException::class );
		$repository->getApplicationById( self::ID_OF_APPLICATION_NOT_IN_DB );
	}

	public function testWhenApplicationAlreadyExists_persistingCausesUpdate(): void {
		$repository = $this->newRepository();
		$originalApplication = ValidMembershipApplication::newDomainEntity();

		$repository->storeApplication( $originalApplication );

		// It is important a new instance is created here to test "detached entity" handling
		$newApplication = ValidMembershipApplication::newDomainEntity();
		$newApplication->assignId( $originalApplication->getId() );
		$newApplication->getApplicant()->changeEmailAddress( new EmailAddress( 'chuck.norris@always.win' ) );

		$repository->storeApplication( $newApplication );

		$doctrineApplication = $this->getApplicationFromDatabase( $newApplication->getId() );

		$this->assertSame( 'chuck.norris@always.win', $doctrineApplication->getApplicantEmailAddress() );
	}

	public function testWriteAndReadRoundtrip(): void {
		$repository = $this->newRepository();
		$application = ValidMembershipApplication::newAutoConfirmedDomainEntity();

		$repository->storeApplication( $application );

		$this->assertEquals(
			$application,
			$repository->getApplicationById( self::MEMBERSHIP_APPLICATION_ID )
		);
	}

	public function testWhenPersistingDeletedApplication_exceptionIsThrown(): void {
		$application = ValidMembershipApplication::newDomainEntity();
		$application->assignId( self::ID_OF_APPLICATION_NOT_IN_DB );

		$repository = $this->newRepository();

		$this->expectException( StoreMembershipApplicationException::class );
		$repository->storeApplication( $application );
	}

	public function testWhenPersistingApplicationWithModerationFlag_doctrineApplicationHasFlag(): void {
		$application = ValidMembershipApplication::newDomainEntity();
		$application->markForModeration();

		$this->newRepository()->storeApplication( $application );
		$doctrineApplication = $this->getApplicationFromDatabase( $application->getId() );

		$this->assertTrue( $doctrineApplication->needsModeration() );
		$this->assertFalse( $doctrineApplication->isCancelled() );
	}

	public function testWhenPersistingApplicationWithCancelledFlag_doctrineApplicationHasFlag(): void {
		$application = ValidMembershipApplication::newDomainEntity();
		$application->cancel();

		$this->newRepository()->storeApplication( $application );
		$doctrineApplication = $this->getApplicationFromDatabase( $application->getId() );

		$this->assertFalse( $doctrineApplication->needsModeration() );
		$this->assertTrue( $doctrineApplication->isCancelled() );
	}

	public function testWhenPersistingCancelledModerationApplication_doctrineApplicationHasFlags(): void {
		$application = ValidMembershipApplication::newDomainEntity();
		$application->markForModeration();
		$application->cancel();

		$this->newRepository()->storeApplication( $application );
		$doctrineApplication = $this->getApplicationFromDatabase( $application->getId() );

		$this->assertTrue( $doctrineApplication->needsModeration() );
		$this->assertTrue( $doctrineApplication->isCancelled() );
	}

	public function testGivenDoctrineApplicationWithModerationAndCancelled_domainEntityHasFlags(): void {
		$doctrineApplication = ValidMembershipApplication::newDoctrineEntity();
		$doctrineApplication->setStatus( DoctrineApplication::STATUS_CANCELED + DoctrineApplication::STATUS_MODERATION );

		$this->storeDoctrineApplication( $doctrineApplication );
		$application = $this->newRepository()->getApplicationById( $doctrineApplication->getId() );

		$this->assertTrue( $application->needsModeration() );
		$this->assertTrue( $application->isCancelled() );
	}

	public function testGivenDoctrineApplicationWithModerationFlag_domainEntityHasFlag(): void {
		$doctrineApplication = ValidMembershipApplication::newDoctrineEntity();
		$doctrineApplication->setStatus( DoctrineApplication::STATUS_MODERATION );

		$this->storeDoctrineApplication( $doctrineApplication );
		$application = $this->newRepository()->getApplicationById( $doctrineApplication->getId() );

		$this->assertTrue( $application->needsModeration() );
		$this->assertFalse( $application->isCancelled() );
	}

	public function testGivenDoctrineApplicationWithCancelledFlag_domainEntityHasFlag(): void {
		$doctrineApplication = ValidMembershipApplication::newDoctrineEntity();
		$doctrineApplication->setStatus( DoctrineApplication::STATUS_CANCELED );

		$this->storeDoctrineApplication( $doctrineApplication );
		$application = $this->newRepository()->getApplicationById( $doctrineApplication->getId() );

		$this->assertFalse( $application->needsModeration() );
		$this->assertTrue( $application->isCancelled() );
	}

	public function testGivenDoctrineApplicationWithCancelledFlag_initialStatusIsPreserved(): void {
		$application = ValidMembershipApplication::newDomainEntity();
		$application->cancel();

		$this->newRepository()->storeApplication( $application );
		$doctrineApplication = $this->getApplicationFromDatabase( $application->getId() );

		$this->assertSame( DoctrineApplication::STATUS_CONFIRMED, $doctrineApplication->getDataObject()->getPreservedStatus() );
	}

	public function testGivenCompanyApplication_companyNameIsPersisted(): void {
		$this->newRepository()->storeApplication( ValidMembershipApplication::newCompanyApplication() );

		$expectedDoctrineEntity = ValidMembershipApplication::newDoctrineCompanyEntity();
		$expectedDoctrineEntity->setId( self::MEMBERSHIP_APPLICATION_ID );

		$this->assertDoctrineEntityIsInDatabase( $expectedDoctrineEntity );
	}

}
