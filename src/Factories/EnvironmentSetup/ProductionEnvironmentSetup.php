<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories\EnvironmentSetup;

use Doctrine\ORM\Tools\Setup;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Factories\LoggerFactory;
use WMDE\Fundraising\Frontend\Presentation\Presenters\InternalErrorHtmlPresenter;

class ProductionEnvironmentSetup implements EnvironmentSetup {

	public function setEnvironmentDependentInstances( FunFunFactory $factory, array $configuration ) {
		$this->initializeLoggers( $factory, $configuration['logging'] );

		$factory->enableCaching();
	}

	private function initializeLoggers( FunFunFactory $factory, array $loggingConfig ) {
		$this->setApplicationLogger( $factory, $loggingConfig );
		$this->setPaypalLogger( $factory );
		$this->setSofortLogger( $factory );
		$this->setCreditCardLogger( $factory );
		$this->setDoctrineConfiguration( $factory );
	}

	private function setApplicationLogger( FunFunFactory $factory, array $loggingConfig ) {
		$factory->setLogger(
			( new LoggerFactory( $loggingConfig ) )
				->getLogger()
		);
	}

	private function setPaypalLogger( FunFunFactory $factory ) {
		$factory->setPaypalLogger( $this->createStreamLoggerForPayment( 'paypal', $factory->getLoggingPath() ) );
	}

	private function setSofortLogger( FunFunFactory $factory ) {
		$factory->setSofortLogger( $this->createStreamLoggerForPayment( 'sofort', $factory->getLoggingPath() ) );
	}

	private function setDoctrineConfiguration( FunFunFactory $factory ) {
		// Setup will choose its own caching (APCu, Redis, Memcached, Array) based on the PHP environment and its extensions.
		// See https://phabricator.wikimedia.org/T249338
		$factory->setDoctrineConfiguration( Setup::createConfiguration( false, $factory->getWritableApplicationDataPath() . '/doctrine_proxies' ) );
	}

	private function setCreditCardLogger( FunFunFactory $factory ) {
		$factory->setCreditCardLogger( $this->createStreamLoggerForPayment( 'creditcard', $factory->getLoggingPath() ) );
	}

	private function createStreamLoggerForPayment( string $paymentName, string $loggingPath ): LoggerInterface {
		$logger = new Logger( $paymentName );
		$streamHandler = new StreamHandler( $loggingPath . '/' . $paymentName . '.log' );
		$streamHandler->setFormatter( new JsonFormatter() );
		$logger->pushHandler( $streamHandler );
		return $logger;
	}
}
