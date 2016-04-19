<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class WebRouteTestSetupTest extends WebRouteTestCase {

	public function testPersistenceGetsInitialized() {
		$factory = TestEnvironment::newInstance()->getFactory();

		$tableNames = $this->removeDatabaseNames(
			...$factory->getConnection()->getSchemaManager()->createSchema()->getTableNames()
		);

		$this->assertContains( 'spenden', $tableNames );
		$this->assertContains( 'users', $tableNames );
	}

	private function removeDatabaseNames( string ...$tableNames ): array {
		return array_map(
			function( string $tableName ) {
				$parts = explode( '.', $tableName );
				return end( $parts );
			},
			$tableNames
		);
	}

}
