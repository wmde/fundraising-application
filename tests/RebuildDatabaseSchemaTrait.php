<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests;

use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

trait RebuildDatabaseSchemaTrait {
	public static function rebuildDatabaseSchema( FunFunFactory $factory ): void {
		$schemaCreator = new SchemaCreator( $factory->getPlainEntityManager() );

		try {
			$schemaCreator->dropSchema();
		}
		catch ( \Exception $ex ) {
		}

		$schemaCreator->createSchema();
	}

}
