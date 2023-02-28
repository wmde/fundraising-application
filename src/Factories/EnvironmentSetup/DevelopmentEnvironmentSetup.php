<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories\EnvironmentSetup;

use Doctrine\ORM\ORMSetup;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DevelopmentInternalErrorHtmlPresenter;

class DevelopmentEnvironmentSetup implements EnvironmentSetup {

	private ErrorLogHandler $logHandler;

	public function setEnvironmentDependentInstances( FunFunFactory $factory ): void {
		$this->logHandler = new ErrorLogHandler();
		$this->setPaypalLogger( $factory );
		$this->setSofortLogger( $factory );
		$this->setDoctrineConfiguration( $factory );
		$this->setErrorPageHtmlPresenter( $factory );
	}

	private function setErrorPageHtmlPresenter( FunFunFactory $factory ): void {
		$factory->setInternalErrorHtmlPresenter(
			new DevelopmentInternalErrorHtmlPresenter()
		);
	}

	private function setPaypalLogger( FunFunFactory $factory ): void {
		$logger = new Logger( 'paypal', [ $this->logHandler ] );
		$factory->setPaypalLogger( $logger );
	}

	private function setSofortLogger( FunFunFactory $factory ): void {
		$logger = new Logger( 'sofort', [ $this->logHandler ] );
		$factory->setSofortLogger( $logger );
	}

	private function setDoctrineConfiguration( FunFunFactory $factory ): void {
		// Setup will use /tmp for proxies and ArrayCache for caching
		$factory->setDoctrineConfiguration( ORMSetup::createXMLMetadataConfiguration( $factory->getDoctrineXMLMappingPaths(), true ) );
	}

}
