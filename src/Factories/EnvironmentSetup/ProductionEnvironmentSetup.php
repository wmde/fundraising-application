<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories\EnvironmentSetup;

use Doctrine\ORM\Tools\Setup;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class ProductionEnvironmentSetup implements EnvironmentSetup {

	public function setEnvironmentDependentInstances( FunFunFactory $factory ) {
		$this->setCampaignCache( $factory );
		$this->initializeLoggers( $factory );
	}

	private function initializeLoggers( FunFunFactory $factory ) {
		$this->setPaypalLogger( $factory );
		$this->setSofortLogger( $factory );
		$this->setCreditCardLogger( $factory );
		$this->setDoctrineConfiguration( $factory );
	}

	private function setCampaignCache( FunFunFactory $factory ) {
		$factory->setCampaignCache(
			new Psr16Cache(
				new FilesystemAdapter( 'campaigns', 60 * 60 * 24 * 7, $factory->getCachePath() . '/campaigns' )
			)
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
