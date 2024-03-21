<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\EnvironmentSetup;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class FakeEnvironmentSetup implements EnvironmentSetup {
	public function setEnvironmentDependentInstances( FunFunFactory $factory ): void {
		// nothing to do here
	}

}
