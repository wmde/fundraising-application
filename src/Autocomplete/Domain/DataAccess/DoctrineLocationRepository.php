<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Autocomplete\Domain\DataAccess;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Frontend\Autocomplete\Domain\LocationRepository;
use WMDE\Fundraising\Frontend\Autocomplete\Domain\Model\Location;

class DoctrineLocationRepository implements LocationRepository {

	private EntityManager $entityManager;

	public function __construct( EntityManager $entityManager ) {
		$this->entityManager = $entityManager;
	}

	public function getCitiesForPostcode( string $postcode ): array {
		$queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
		$metaData = $this->entityManager->getClassMetadata( Location::class );

		$cityField = $metaData->getColumnName( 'cityName' );
		$postcodeField = $metaData->getColumnName( 'postcode' );

		$query = $queryBuilder->select( "DISTINCT $cityField" )
			->from( $metaData->getTableName() )
			->where( $queryBuilder->expr()->eq( $postcodeField, $postcode ) )
			->execute();

		return $query->fetchFirstColumn();
	}
}
