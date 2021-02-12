<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\EnvironmentSetup;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * @license GPL-2.0-or-later
 */
class FakeEnvironmentSetup implements EnvironmentSetup {
	public function setEnvironmentDependentInstances( FunFunFactory $factory ) {
		// nothing to do here
	}

}
