<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Autocomplete\Domain\DataAccess;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Frontend\Autocomplete\Domain\LocationRepository;
use WMDE\Fundraising\Frontend\Autocomplete\Domain\Model\Location;

class DoctrineLocationRepository implements LocationRepository {

	public function __construct( private readonly EntityManager $entityManager ) {
	}

	/**
	 * @return string[]
	 */
	public function getCitiesForPostcode( string $postcode ): array {
		$queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
		$metaData = $this->entityManager->getClassMetadata( Location::class );

		$communityField = $metaData->getColumnName( 'communityName' );
		$postcodeField = $metaData->getColumnName( 'postcode' );

		$query = $queryBuilder->select( "DISTINCT $communityField" )
			->from( $metaData->getTableName() )
			->where( $queryBuilder->expr()->eq( $postcodeField, ':postcode' ) )
			->orderBy( $communityField )
			->setParameter( 'postcode', $postcode )
			->executeQuery();

		return $query->fetchFirstColumn();
	}

	/**
	 * @return string[]
	 */
	public function getStreetsForPostcode( string $postcode ): array {
		$queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
		$metaData = $this->entityManager->getClassMetadata( Location::class );

		$streetField = $metaData->getColumnName( 'street' );
		$postcodeField = $metaData->getColumnName( 'postcode' );

		$query = $queryBuilder->select( "DISTINCT $streetField" )
			->from( $metaData->getTableName() )
			->where( $queryBuilder->expr()->eq( $postcodeField, ':postcode' ) )
			->orderBy( $streetField )
			->setParameter( 'postcode', $postcode )
			->executeQuery();

		return $query->fetchFirstColumn();
	}
}
