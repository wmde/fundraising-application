<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Autocomplete\DataAccess;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WMDE\Fundraising\Frontend\Autocomplete\Domain\DataAccess\DoctrineLocationRepository;
use WMDE\Fundraising\Frontend\Autocomplete\Domain\Model\Location;
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
		$factory = static::$container->get( FunFunFactory::class );
		static::rebuildDatabaseSchema( $factory );
		$this->entityManager = $factory->getEntityManager();
	}

	public function testGivenPostcode_returnsDistinctCities(): void {
		$this->insertLocationForPostcodeAndCity( '12345', 'Wexford' );
		$this->insertLocationForPostcodeAndCity( '12345', 'Wexford' );
		$this->insertLocationForPostcodeAndCity( '12345', 'Waterford' );
		$this->insertLocationForPostcodeAndCity( '34567', 'Kildare' );
		$this->insertLocationForPostcodeAndCity( '12345', 'Wicklow' );

		$locationRepository = new DoctrineLocationRepository( $this->entityManager );

		$cities = $locationRepository->getCitiesForPostcode( '12345' );

		$this->assertSame( [ 'Wexford', 'Waterford', 'Wicklow' ], $cities );
	}

	private function insertLocationForPostcodeAndCity( string $postcode, string $city ): void {
		$location = ValidLocation::validLocationForPostcodeAndCity( $postcode, $city );
		$this->entityManager->persist( $location );
		$this->entityManager->flush();
	}
}
