<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\System;

use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class WebRouteTestSetupTest extends WebRouteTestCase {

	public function testPersistenceGetsInitialized() {
		$factory = TestEnvironment::newInstance()->getFactory();

		$tableNames = $factory->getConnection()->getSchemaManager()->createSchema()->getTableNames();

		$this->assertContains( 'public.spenden', $tableNames );
		$this->assertContains( 'public.users', $tableNames );
	}

}
