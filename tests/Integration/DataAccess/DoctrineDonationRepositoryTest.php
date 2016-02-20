<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\Integration\DataAccess;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineDonationRepository;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;
use \WMDE\Fundraising\Entities\Donation as DoctineDonation;

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

		// FIXME: test fails when using 100.01 as amount
		$this->assertSame( $donation->getAmount(), $doctrineDonation->getAmount() );
	}

	private function getDonationFromDatabase(): DoctineDonation {
		$donationRepo = $this->entityManager->getRepository( DoctineDonation::class );
		$donation = $donationRepo->find( 1 );
		$this->assertInstanceOf( DoctineDonation::class, $donation );
		return $donation;
	}

}
