<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories\EnvironmentSetup;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Factories\LoggerFactory;

class DevelopmentEnvironmentSetup implements EnvironmentSetup {

	private $logHandler;

	public function setEnvironmentDependentInstances( FunFunFactory $factory, array $configuration ) {
		$this->logHandler = new ErrorLogHandler();
		$this->setApplicationLogger( $factory, $configuration['logging'] );
		$this->setPaypalLogger( $factory );
		$this->setSofortLogger( $factory );
	}

	private function setApplicationLogger( FunFunFactory $factory, array $loggingConfig ) {
		$factory->setLogger(
			( new LoggerFactory( $loggingConfig ) )
				->getLogger()
		);
	}

	private function setPaypalLogger( FunFunFactory $factory ) {
		$logger = new Logger( 'paypal', [ $this->logHandler ] );
		$factory->setPaypalLogger( $logger );
	}

	private function setSofortLogger( FunFunFactory $factory ) {
		$logger = new Logger( 'sofort', [ $this->logHandler ] );
		$factory->setSofortLogger( $logger );
	}

}