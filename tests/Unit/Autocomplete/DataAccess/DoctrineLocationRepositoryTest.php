<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Autocomplete\DataAccess;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WMDE\Fundraising\Frontend\Autocomplete\Domain\DataAccess\DoctrineLocationRepository;
use WMDE\Fundraising\Frontend\Autocomplete\Domain\Model\Location;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\Data\ValidLocation;
use WMDE\Fundraising\Frontend\Tests\RebuildDatabaseSchemaTrait;

#[CoversClass( DoctrineLocationRepository::class )]
#[CoversClass( Location::class )]
class DoctrineLocationRepositoryTest extends KernelTestCase {

	use RebuildDatabaseSchemaTrait;

	private EntityManager $entityManager;

	public function setUp(): void {
		parent::setUp();
		static::bootKernel();

		/** @var FunFunFactory $factory */
		$factory = static::getContainer()->get( FunFunFactory::class );
		static::rebuildDatabaseSchema( $factory );
		$this->entityManager = $factory->getEntityManager();
	}

	public function testGivenPostcode_returnsDistinctCities(): void {
		$this->entityManager->persist( ValidLocation::newValidLocation( '12345', 'Wexford', 'Sesame' ) );
		$this->entityManager->persist( ValidLocation::newValidLocation( '12345', 'Wexford', 'Sesame' ) );
		$this->entityManager->persist( ValidLocation::newValidLocation( '12345', 'Waterford', 'Sesame' ) );
		$this->entityManager->persist( ValidLocation::newValidLocation( '34567', 'Kildare', 'Sesame' ) );
		$this->entityManager->persist( ValidLocation::newValidLocation( '12345', 'Wicklow', 'Sesame' ) );
		$this->entityManager->flush();

		$locationRepository = new DoctrineLocationRepository( $this->entityManager );

		$cities = $locationRepository->getCitiesForPostcode( '12345' );

		$this->assertSame( [ 'Waterford', 'Wexford', 'Wicklow' ], $cities );
	}

	public function testGivenPostcode_returnsDistinctStreets(): void {
		$this->entityManager->persist( ValidLocation::newValidLocation( '12345', 'Wexford', 'Sesame' ) );
		$this->entityManager->persist( ValidLocation::newValidLocation( '12345', 'Wexford', 'Sesame' ) );
		$this->entityManager->persist( ValidLocation::newValidLocation( '12345', 'Waterford', 'Elm' ) );
		$this->entityManager->persist( ValidLocation::newValidLocation( '34567', 'Kildare', 'Respectable' ) );
		$this->entityManager->persist( ValidLocation::newValidLocation( '12345', 'Wicklow', 'Abbey Road' ) );
		$this->entityManager->flush();

		$locationRepository = new DoctrineLocationRepository( $this->entityManager );

		$streets = $locationRepository->getStreetsForPostcode( '12345' );

		$this->assertSame( [ 'Abbey Road', 'Elm', 'Sesame' ], $streets );
	}

	public function testVeryMaliciousSQLInjectionInPostCodeDoesNothing(): void {
		$this->entityManager->persist( ValidLocation::newValidLocation( '12345', 'Wexford', 'Sesame' ) );
		$this->entityManager->persist( ValidLocation::newValidLocation( '12345', 'Waterford', 'Sesame' ) );
		$this->entityManager->flush();

		$locationRepository = new DoctrineLocationRepository( $this->entityManager );

		$locationRepository->getCitiesForPostcode( '0; DELETE FROM geodaten_artikelnr_1050; --' );
		$locationRepository->getStreetsForPostcode( '0; DELETE FROM geodaten_artikelnr_1050; --' );

		$remainingRows = $this->entityManager->getConnection()->executeQuery( "SELECT COUNT(*) FROM geodaten_artikelnr_1050" )->fetchOne();
		$this->assertEquals( 2, $remainingRows );
	}

	public function testMalformedSQLInjectionInPostCodeDoesNothing(): void {
		$this->entityManager->persist( ValidLocation::newValidLocation( '12345', 'Wexford', 'Sesame' ) );
		$this->entityManager->persist( ValidLocation::newValidLocation( '12345', 'Waterford', 'Sesame' ) );
		$this->entityManager->flush();

		$locationRepository = new DoctrineLocationRepository( $this->entityManager );

		$cities = $locationRepository->getCitiesForPostcode( '666 OR 1' );
		$streets = $locationRepository->getStreetsForPostcode( '666 OR 1' );

		$this->assertSame( [], $cities );
		$this->assertSame( [], $streets );
	}
}
