<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\DataAccess;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Entities\MembershipApplication as DoctrineMembershipApplication;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineMembershipApplicationRepository;
use WMDE\Fundraising\Frontend\Domain\Repositories\StoreMembershipApplicationException;
use WMDE\Fundraising\Frontend\Tests\Data\ValidMembershipApplication;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ThrowingEntityManager;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @covers WMDE\Fundraising\Frontend\DataAccess\DoctrineMembershipApplicationRepository
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineMembershipApplicationRepositoryTest extends \PHPUnit_Framework_TestCase {

	const MEMBERSHIP_APPLICATION_ID = 1;

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	public function setUp() {
		$this->entityManager = TestEnvironment::newInstance()->getFactory()->getEntityManager();
		parent::setUp();
	}

	public function testValidApplicationGetPersisted() {
		$application = ValidMembershipApplication::newDomainEntity();

		( new DoctrineMembershipApplicationRepository( $this->entityManager ) )->storeApplication( $application );

		$expectedDoctrineEntity = ValidMembershipApplication::newDoctrineEntity();
		$expectedDoctrineEntity->setId( self::MEMBERSHIP_APPLICATION_ID );

		$this->assertDoctrineEntityInDatabase( $expectedDoctrineEntity );
	}

	private function assertDoctrineEntityInDatabase( DoctrineMembershipApplication $expected ) {
		$actual = $this->getApplicationFromDatabase( $expected->getId() );
		$actual->setTimestamp( null ); // TODO: gabriel, suggestion of how to test this?

		$this->assertEquals( $expected, $actual );
	}

	private function getApplicationFromDatabase( int $id ): DoctrineMembershipApplication {
		$applicationRepo = $this->entityManager->getRepository( DoctrineMembershipApplication::class );
		$donation = $applicationRepo->find( $id );
		$this->assertInstanceOf( DoctrineMembershipApplication::class, $donation );
		return $donation;
	}

	public function testIdGetsAssigned() {
		$application = ValidMembershipApplication::newDomainEntity();

		( new DoctrineMembershipApplicationRepository( $this->entityManager ) )->storeApplication( $application );

		$this->assertSame( self::MEMBERSHIP_APPLICATION_ID, $application->getId() );
	}

	public function testWhenPersistenceFails_domainExceptionIsThrown() {
		$donation = ValidMembershipApplication::newDomainEntity();

		$repository = new DoctrineMembershipApplicationRepository( ThrowingEntityManager::newInstance( $this ) );

		$this->expectException( StoreMembershipApplicationException::class );
		$repository->storeApplication( $donation );
	}

}
