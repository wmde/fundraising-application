<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\Mapping\ClassMetadata;

class SchemaCreator {
	private EntityManager $entityManager;

	public function __construct( EntityManager $entityManager ) {
		$this->entityManager = $entityManager;
	}

	public function createSchema(): void {
		$this->getSchemaTool()->createSchema( $this->getClassMetaData() );
	}

	public function dropSchema(): void {
		$this->getSchemaTool()->dropSchema( $this->getClassMetaData() );
	}

	private function getSchemaTool(): SchemaTool {
		return new SchemaTool( $this->entityManager );
	}

	/**
	 * @return ClassMetadata[]
	 */
	private function getClassMetaData(): array {
		return $this->entityManager->getMetadataFactory()->getAllMetadata();
	}

}