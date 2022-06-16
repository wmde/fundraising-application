<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests;

use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentId;

trait RebuildDatabaseSchemaTrait {
	public static function rebuildDatabaseSchema( FunFunFactory $factory ): void {
		$entityManager = $factory->getPlainEntityManager();
		$schemaCreator = new SchemaCreator( $entityManager );

		try {
			$schemaCreator->dropSchema();
		}
		catch ( \Exception $ex ) {
		}

		$schemaCreator->createSchema();
		$entityManager->persist( new PaymentId() );
		$entityManager->flush();
	}

}
