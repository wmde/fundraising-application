<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories\EnvironmentSetup;

use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

interface EnvironmentSetup {
	public function setEnvironmentDependentInstances( FunFunFactory $factory );
}