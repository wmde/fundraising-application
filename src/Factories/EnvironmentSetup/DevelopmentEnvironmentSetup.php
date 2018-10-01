<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories\EnvironmentSetup;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class DevelopmentEnvironmentSetup  implements EnvironmentSetup {

	private $logHandler;

	public function setEnvironmentDependentInstances( FunFunFactory $factory ) {
		$this->logHandler = new ErrorLogHandler();
		$this->setApplicationLogger( $factory );
		$this->setPaypalLogger( $factory );
		$this->setSofortLogger( $factory );
	}

	private function setApplicationLogger( FunFunFactory $factory ) {
		$logger = new Logger( 'index_php' );
		$logger->pushHandler( $this->logHandler );
		$factory->setLogger( $logger );
	}

	private function setPaypalLogger( FunFunFactory $factory ) {
		$logger = new Logger( 'paypal' );
		$logger->pushHandler( $this->logHandler );
		$factory->setPaypalLogger( $logger );
	}

	private function setSofortLogger( FunFunFactory $factory ) {
		$logger = new Logger( 'sofort' );
		$logger->pushHandler( $this->logHandler );
		$factory->setSofortLogger( $logger );
	}

}