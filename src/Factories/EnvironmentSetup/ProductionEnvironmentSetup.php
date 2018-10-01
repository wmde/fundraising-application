<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories\EnvironmentSetup;

use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class ProductionEnvironmentSetup implements EnvironmentSetup {

	public function setEnvironmentDependentInstances( FunFunFactory $factory ) {
		$this->initializeLoggers( $factory );

		$factory->enableCaching();
	}

	private function initializeLoggers( FunFunFactory $factory ) {
		$this->setApplicationLogger( $factory );
		$this->setPaypalLogger( $factory );
		$this->setSofortLogger( $factory );
	}

	private function setApplicationLogger( FunFunFactory $factory ) {
		$logger = new Logger( 'index_php' );

		$streamHandler = new StreamHandler(
			$factory->getLoggingPath() . '/error-debug.log'
		);

		$fingersCrossedHandler = new FingersCrossedHandler( $streamHandler );
		$streamHandler->setFormatter( new LineFormatter( LineFormatter::SIMPLE_FORMAT ) );
		$logger->pushHandler( $fingersCrossedHandler );

		$errorHandler = new StreamHandler(
			$factory->getLoggingPath() . '/error.log',
			Logger::ERROR
		);
		$errorHandler->setFormatter( new JsonFormatter() );
		$logger->pushHandler( $errorHandler );

		$factory->setLogger( $logger );
	}

	private function setPaypalLogger( FunFunFactory $factory ) {
		$logger = new Logger( 'paypal' );

		$streamHandler = new StreamHandler(
			$factory->getLoggingPath() . '/paypal.log'
		);
		$streamHandler->setFormatter( new JsonFormatter() );
		$logger->pushHandler( $streamHandler );

		$factory->setPaypalLogger( $logger );
	}

	private function setSofortLogger( FunFunFactory $factory ) {
		$logger = new Logger( 'sofort' );

		$streamHandler = new StreamHandler( $factory->getLoggingPath() . '/sofort.log' );
		$streamHandler->setFormatter( new JsonFormatter() );
		$logger->pushHandler( $streamHandler );

		$factory->setSofortLogger( $logger );
	}
}
