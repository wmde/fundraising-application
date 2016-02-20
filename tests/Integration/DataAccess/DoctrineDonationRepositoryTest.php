<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\Integration\DataAccess;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use WMDE\Fundraising\Entities\Donation as DoctineDonation;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineDonationRepository;
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

	private function getDonationFromDatabase(): DoctineDonation {
		$donationRepo = $this->entityManager->getRepository( DoctineDonation::class );
		$donation = $donationRepo->find( 1 );
		$this->assertInstanceOf( DoctineDonation::class, $donation );
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

}
