<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Autocomplete\DataAccess;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WMDE\Fundraising\Frontend\Autocomplete\Domain\DataAccess\DoctrineLocationRepository;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\Data\ValidLocation;
use WMDE\Fundraising\Frontend\Tests\RebuildDatabaseSchemaTrait;

/**
 * @covers \WMDE\Fundraising\Frontend\Autocomplete\Domain\DataAccess\DoctrineLocationRepository
 * @covers \WMDE\Fundraising\Frontend\Autocomplete\Domain\Model\Location
 */
class DoctrineLocationRepositoryTest extends KernelTestCase {

	use RebuildDatabaseSchemaTrait;

	private EntityManager $entityManager;

	public function setUp(): void {
		parent::setUp();
		static::bootKernel();
		$factory = static::getContainer()->get( FunFunFactory::class );
		static::rebuildDatabaseSchema( $factory );
		$this->entityManager = $factory->getEntityManager();
	}

	public function testGivenPostcode_returnsDistinctCities(): void {
		$this->entityManager->persist( ValidLocation::validLocationForCommunity( '12345', 'Wexford' ) );
		$this->entityManager->persist( ValidLocation::validLocationForCommunity( '12345', 'Wexford' ) );
		$this->entityManager->persist( ValidLocation::validLocationForCommunity( '12345', 'Waterford' ) );
		$this->entityManager->persist( ValidLocation::validLocationForCommunity( '34567', 'Kildare' ) );
		$this->entityManager->persist( ValidLocation::validLocationForCommunity( '12345', 'Wicklow' ) );
		$this->entityManager->flush();

		$locationRepository = new DoctrineLocationRepository( $this->entityManager );

		$cities = $locationRepository->getCitiesForPostcode( '12345' );

		$this->assertSame( [ 'Waterford', 'Wexford', 'Wicklow' ], $cities );
	}

	public function testVeryMaliciousSQLInjectionInPostCodeDoesNothing(): void {
		$this->entityManager->persist( ValidLocation::validLocationForCommunity( '12345', 'Wexford' ) );
		$this->entityManager->persist( ValidLocation::validLocationForCommunity( '12345', 'Waterford' ) );
		$this->entityManager->flush();

		$locationRepository = new DoctrineLocationRepository( $this->entityManager );

		$locationRepository->getCitiesForPostcode( '0; DELETE FROM geodaten_artikelnr_1001; --' );

		$remainingRows = $this->entityManager->getConnection()->executeQuery( "SELECT COUNT(*) FROM geodaten_artikelnr_1001" )->fetchOne();
		$this->assertEquals( 2, $remainingRows );
	}

	public function testMalformedSQLInjectionInPostCodeDoesNothing(): void {
		$this->entityManager->persist( ValidLocation::validLocationForCommunity( '12345', 'Wexford' ) );
		$this->entityManager->persist( ValidLocation::validLocationForCommunity( '12345', 'Waterford' ) );
		$this->entityManager->flush();

		$locationRepository = new DoctrineLocationRepository( $this->entityManager );

		$cities = $locationRepository->getCitiesForPostcode( '666 OR 1' );

		$this->assertSame( [], $cities );
	}
}
