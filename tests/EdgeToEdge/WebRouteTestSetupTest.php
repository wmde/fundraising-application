<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @covers \WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase
 */
class WebRouteTestSetupTest extends WebRouteTestCase {

	public function testPersistenceGetsInitialized(): void {
		$factory = TestEnvironment::newInstance()->getFactory();

		$tableNames = $this->removeDatabaseNames(
			...$factory->getConnection()->getSchemaManager()->createSchema()->getTableNames()
		);

		$this->assertContains( 'spenden', $tableNames );
	}

	private function removeDatabaseNames( string ...$tableNames ): array {
		return array_map(
			function ( string $tableName ) {
				$parts = explode( '.', $tableName );
				return end( $parts );
			},
			$tableNames
		);
	}

}
