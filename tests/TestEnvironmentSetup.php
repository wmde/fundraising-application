<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests;

use Doctrine\ORM\Tools\Setup;
use Symfony\Component\Translation\Translator;
use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\EnvironmentSetup;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DevelopmentInternalErrorHtmlPresenter;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeUrlGenerator;

/**
 * @license GPL-2.0-or-later
 */
class TestEnvironmentSetup implements EnvironmentSetup {
	public function setEnvironmentDependentInstances( FunFunFactory $factory, array $configuration ) {
		$factory->setNullMessenger();
		$factory->setUrlGenerator( new FakeUrlGenerator() );

		$factory->setDoctrineConfiguration( Setup::createConfiguration( true ) );
		$factory->setInternalErrorHtmlPresenter( new DevelopmentInternalErrorHtmlPresenter() );
	}
}
