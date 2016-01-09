<?php

namespace WMDE\Fundraising\Frontend\Tests\System;

use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SystemTestSetupTest extends SystemTestCase {

	public function testPersistenceGetsInitialized() {
		$factory = TestEnvironment::newInstance()->getFactory();

		$tableNames = $factory->getConnection()->getSchemaManager()->createSchema()->getTableNames();

		$this->assertContains( 'public.spenden', $tableNames );
		$this->assertContains( 'public.users', $tableNames );
	}

}
