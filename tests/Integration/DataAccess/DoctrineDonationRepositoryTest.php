<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\DataAccess;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Entities\Donation as DoctrineDonation;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineDonationRepository;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Repositories\GetDonationException;
use WMDE\Fundraising\Frontend\Domain\Repositories\StoreDonationException;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ThrowingEntityManager;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @covers WMDE\Fundraising\Frontend\DataAccess\DoctrineDonationRepository
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineDonationRepositoryTest extends \PHPUnit_Framework_TestCase {

	const ID_OF_DONATION_NOT_IN_DB = 35505;

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	public function setUp() {
		$this->entityManager = TestEnvironment::newInstance()->getFactory()->getEntityManager();
		parent::setUp();
	}

	public function testValidDonationGetPersisted() {
		$donation = ValidDonation::newDirectDebitDonation();

		( new DoctrineDonationRepository( $this->entityManager ) )->storeDonation( $donation );

		$doctrineDonation = $this->getDonationFromDatabase();

		// TODO: compare whole donation, now easy via ValidDonation
		$this->assertSame( $donation->getAmount()->getEuroString(), $doctrineDonation->getAmount() );
		$this->assertSame( $donation->getDonor()->getEmailAddress(), $doctrineDonation->getDonorEmail() );
	}

	private function getDonationFromDatabase(): DoctrineDonation {
		$donationRepo = $this->entityManager->getRepository( DoctrineDonation::class );
		$donation = $donationRepo->find( 1 );
		$this->assertInstanceOf( DoctrineDonation::class, $donation );
		return $donation;
	}

	public function testFractionalAmountsRoundtripWithoutChange() {
		$donation = ValidDonation::newDirectDebitDonation();

		( new DoctrineDonationRepository( $this->entityManager ) )->storeDonation( $donation );

		$doctrineDonation = $this->getDonationFromDatabase();

		$this->assertSame( $donation->getAmount()->getEuroString(), $doctrineDonation->getAmount() );
	}

	public function testWhenPersistenceFails_domainExceptionIsThrown() {
		$donation = ValidDonation::newDirectDebitDonation();

		$repository = new DoctrineDonationRepository( ThrowingEntityManager::newInstance( $this ) );

		$this->expectException( StoreDonationException::class );
		$repository->storeDonation( $donation );
	}

	public function testNewDonationPersistenceRoundTrip() {
		$donation = ValidDonation::newDirectDebitDonation();

		$repository = new DoctrineDonationRepository( $this->entityManager );

		$repository->storeDonation( $donation );

		$this->assertEquals(
			$donation,
			$repository->getDonationById( $donation->getId() )
		);
	}

	public function testWhenDonationAlreadyExists_persistingCausesUpdate() {
		$repository = new DoctrineDonationRepository( $this->entityManager );

		$donation = ValidDonation::newDirectDebitDonation();
		$repository->storeDonation( $donation );

		// It is important a new instance is created here to test "detached entity" handling
		$newDonation = ValidDonation::newDirectDebitDonation();
		$newDonation->assignId( $donation->getId() );
		$newDonation->cancel();
		$repository->storeDonation( $newDonation );

		$this->assertEquals( $newDonation, $repository->getDonationById( $newDonation->getId() ) );
	}

	public function testWhenDonationDoesNotExist_getDonationReturnsNull() {
		$repository = new DoctrineDonationRepository( $this->entityManager );

		$this->assertNull( $repository->getDonationById( self::ID_OF_DONATION_NOT_IN_DB ) );
	}

	public function testWhenDoctrineThrowsException_domainExceptionIsThrown() {
		$repository = new DoctrineDonationRepository( ThrowingEntityManager::newInstance( $this ) );

		$this->expectException( GetDonationException::class );
		$repository->getDonationById( self::ID_OF_DONATION_NOT_IN_DB );
	}

	public function testWhenDonationDoesNotExist_persistingCausesException() {
		$donation = ValidDonation::newDirectDebitDonation();
		$donation->assignId( self::ID_OF_DONATION_NOT_IN_DB );

		$repository = new DoctrineDonationRepository( $this->entityManager );

		$this->expectException( StoreDonationException::class );
		$repository->storeDonation( $donation );
	}

	public function testWhenDeletionDateGetsSet_repositoryNoLongerReturnsEntity() {
		$donation = $this->createDeletedDonation();
		$repository = new DoctrineDonationRepository( $this->entityManager );

		$this->assertNull( $repository->getDonationById( $donation->getId() ) );
	}

	private function createDeletedDonation(): Donation {
		$donation = ValidDonation::newDirectDebitDonation();
		$repository = new DoctrineDonationRepository( $this->entityManager );
		$repository->storeDonation( $donation );
		$doctrineDonation = $repository->getDoctrineDonationById( $donation->getId() );
		$doctrineDonation->setDeletionTime( new \DateTime() );
		$this->entityManager->flush();
		return $donation;
	}

	public function testWhenDeletionDateGetsSet_repositoryNoLongerPersistsEntity() {
		$donation = $this->createDeletedDonation();
		$repository = new DoctrineDonationRepository( $this->entityManager );

		$this->expectException( StoreDonationException::class );
		$repository->storeDonation( $donation );
	}

	public function testDataFieldsAreRetainedOrUpdatedOnUpdate() {
		$doctrineDonation = $this->getNewlyCreatedDoctrineDonation();

		$doctrineDonation->encodeAndSetData( array_merge(
			$doctrineDonation->getDecodedData(),
			[
				'untouched' => 'value',
				'vorname' => 'potato',
				'another' => 'untouched',
			]
		) );

		$this->entityManager->flush();

		$donation = ValidDonation::newDirectDebitDonation();
		$donation->assignId( $doctrineDonation->getId() );

		( new DoctrineDonationRepository( $this->entityManager ) )->storeDonation( $donation );

		$data = $this->getDonationFromDatabase()->getDecodedData();

		$this->assertSame( 'value', $data['untouched'] );
		$this->assertSame( 'untouched', $data['another'] );
		$this->assertSame( $donation->getDonor()->getPersonName()->getFirstName(), $data['vorname'] );
	}

	private function getNewlyCreatedDoctrineDonation(): DoctrineDonation {
		$donation = ValidDonation::newDirectDebitDonation();
		( new DoctrineDonationRepository( $this->entityManager ) )->storeDonation( $donation );
		return $this->getDonationFromDatabase();
	}

}
