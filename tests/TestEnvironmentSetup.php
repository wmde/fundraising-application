<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests;

use Symfony\Component\Translation\Translator;
use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\EnvironmentSetup;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeUrlGenerator;

/**
 * @license GNU GPL v2+
 */
class TestEnvironmentSetup implements EnvironmentSetup {
	public function setEnvironmentDependentInstances( FunFunFactory $factory, array $configuration ) {
		$factory->setNullMessenger();
		$factory->setSkinTwigEnvironment( new \Twig_Environment() );
		$factory->setUrlGenerator( new FakeUrlGenerator() );

		// disabling translations in tests (will result in returned keys we can more easily test for)
		$factory->setTranslator( new Translator( 'zz_ZZ' ) );
	}
}