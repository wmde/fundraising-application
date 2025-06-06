<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool;

class SchemaCreator {

	public function __construct( private readonly EntityManager $entityManager ) {
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
	 * @return list<ClassMetadata>
	 * @phpstan-ignore-next-line
	 */
	private function getClassMetaData(): array {
		return $this->entityManager->getMetadataFactory()->getAllMetadata();
	}

}
