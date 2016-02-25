<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\DataAccess;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use WMDE\Fundraising\Entities\Donation as DoctrineDonation;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineDonationRepository;
use WMDE\Fundraising\Frontend\Domain\Repositories\GetDonationException;
use WMDE\Fundraising\Frontend\Domain\Repositories\StoreDonationException;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @covers WMDE\Fundraising\Frontend\DataAccess\DoctrineDonationRepository
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineDonationRepositoryTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	public function setUp() {
		$this->entityManager = TestEnvironment::newInstance()->getFactory()->getEntityManager();
		parent::setUp();
	}

	public function testValidDonationGetPersisted() {
		$donation = ValidDonation::newDonation();

		( new DoctrineDonationRepository( $this->entityManager ) )->storeDonation( $donation );

		$doctrineDonation = $this->getDonationFromDatabase();

		$this->assertSame( $donation->getAmount(), $doctrineDonation->getAmount() );
		$this->assertSame( $donation->getPersonalInfo()->getEmailAddress(), $doctrineDonation->getEmail() );
	}

	private function getDonationFromDatabase(): DoctrineDonation {
		$donationRepo = $this->entityManager->getRepository( DoctrineDonation::class );
		$donation = $donationRepo->find( 1 );
		$this->assertInstanceOf( DoctrineDonation::class, $donation );
		return $donation;
	}

	public function testFractionalAmountsRoundtripWithoutChange() {
		$donation = ValidDonation::newDonation( 100.01 );

		( new DoctrineDonationRepository( $this->entityManager ) )->storeDonation( $donation );

		$doctrineDonation = $this->getDonationFromDatabase();

		$this->assertSame( $donation->getAmount(), $doctrineDonation->getAmount() );
	}

	public function testWhenPersistenceFails_domainExceptionIsThrown() {
		$donation = ValidDonation::newDonation();

		$entityManager = $this->getMockBuilder( EntityManager::class )
			->disableOriginalConstructor()->getMock();

		$entityManager->expects( $this->any() )
			->method( 'persist' )
			->willThrowException( new ORMException() );

		$repository = new DoctrineDonationRepository( $entityManager );

		$this->expectException( StoreDonationException::class );
		$repository->storeDonation( $donation );
	}

	public function testNewDonationPersistenceRoundTrip() {
		$donation = ValidDonation::newDonation();
		$donation->setId( 1 );

		$repository = new DoctrineDonationRepository( $this->entityManager );

		$repository->storeDonation( $donation );

		$this->assertEquals( $donation, $repository->getDonationById( 1 ) );
	}

	public function testExistingDonationPersistenceRoundTrip() {
		$repository = new DoctrineDonationRepository( $this->entityManager );

		$donation = ValidDonation::newDonation();
		$donation->setAmount( 42 );
		$repository->storeDonation( $donation );

		$donation->setAmount( 1337 );
		$repository->storeDonation( $donation );

		$this->assertEquals( $donation, $repository->getDonationById( $donation->getId() ) );
	}

	public function testWhenEntityDoesNotExist_getEntityReturnsNull() {
		$repository = new DoctrineDonationRepository( $this->entityManager );

		$this->assertNull( $repository->getDonationById( 42 ) );
	}

	public function testWhenDoctrineThrowsException_domainExceptionIsThrown() {
		$repository = new DoctrineDonationRepository( $this->newEntityManagerThatThrowsOnFind() );

		$this->expectException( GetDonationException::class );
		$repository->getDonationById( 42 );
	}

	private function newEntityManagerThatThrowsOnFind(): EntityManager {
		$entityManager = $this->getMockBuilder( EntityManager::class )
			->disableOriginalConstructor()->getMock();

		$entityManager->expects( $this->any() )
			->method( 'find' )
			->willThrowException( new ORMException() );

		return $entityManager;
	}

}
