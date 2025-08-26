<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests;

use Doctrine\ORM\ORMSetup;
use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\EnvironmentSetup;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\Validation\NullDomainNameValidator;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DevelopmentInternalErrorHtmlPresenter;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakePayPalAPI;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FeatureReaderStub;

class TestEnvironmentSetup implements EnvironmentSetup {
	public function setEnvironmentDependentInstances( FunFunFactory $factory ): void {
		$factory->setNullMessengers();
		$factory->setDomainNameValidator( new NullDomainNameValidator() );
		$config = ORMSetup::createXMLMetadataConfig(
			$factory->getDoctrineXMLMappingPaths(),
			true
		);
		$config->enableNativeLazyObjects( true );
		$factory->setDoctrineConfiguration( $config );
		$factory->setInternalErrorHtmlPresenter( new DevelopmentInternalErrorHtmlPresenter() );
		$factory->setFeatureReader( new FeatureReaderStub() );
		$factory->setPayPalAPI( new FakePayPalAPI() );
	}
}
