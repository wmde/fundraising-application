<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories\EnvironmentSetup;

use Doctrine\ORM\ORMSetup;
use GuzzleHttp\Client;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\PaymentContext\Services\PayPal\GuzzlePaypalAPI;

class ProductionEnvironmentSetup implements EnvironmentSetup {

	/**
	 * 1 week in seconds
	 */
	private const CACHE_LIFETIME = 604800;

	public function setEnvironmentDependentInstances( FunFunFactory $factory ): void {
		$this->setCampaignCache( $factory );
		$this->initializeLoggers( $factory );
		$this->setDoctrineConfiguration( $factory );
		$this->setPayPalAPIClient( $factory );
	}

	private function initializeLoggers( FunFunFactory $factory ): void {
		$this->setPaypalLogger( $factory );
		$this->setSofortLogger( $factory );
		$this->setCreditCardLogger( $factory );
	}

	private function setCampaignCache( FunFunFactory $factory ): void {
		$factory->setConfigCache(
			new Psr16Cache(
				new FilesystemAdapter( 'configuration_files', self::CACHE_LIFETIME, $factory->getCachePath() . '/configuration_files' )
			)
		);
	}

	private function setPaypalLogger( FunFunFactory $factory ): void {
		$factory->setPaypalLogger( $this->createStreamLoggerForPayment( 'paypal', $factory->getLoggingPath() ) );
	}

	private function setSofortLogger( FunFunFactory $factory ): void {
		$factory->setSofortLogger( $this->createStreamLoggerForPayment( 'sofort', $factory->getLoggingPath() ) );
	}

	private function setDoctrineConfiguration( FunFunFactory $factory ): void {
		$factory->setDoctrineConfiguration( ORMSetup::createXMLMetadataConfiguration(
			$factory->getDoctrineXMLMappingPaths(),
			false,
			$factory->getWritableApplicationDataPath() . '/doctrine_proxies',
			null,
			true
		) );
	}

	private function setCreditCardLogger( FunFunFactory $factory ): void {
		$factory->setCreditCardLogger( $this->createStreamLoggerForPayment( 'creditcard', $factory->getLoggingPath() ) );
	}

	private function createStreamLoggerForPayment( string $paymentName, string $loggingPath ): LoggerInterface {
		$logger = new Logger( $paymentName );
		$streamHandler = new StreamHandler( $loggingPath . '/' . $paymentName . '.log' );
		$streamHandler->setFormatter( new JsonFormatter() );
		$logger->pushHandler( $streamHandler );
		return $logger;
	}

	private function setPayPalAPIClient( FunFunFactory $factory ): void {
		$clientId = $_ENV['PAYPAL_API_CLIENT_ID'] ?? '';
		$secret = $_ENV['PAYPAL_API_CLIENT_SECRET'] ?? '';
		$baseUri = $_ENV['PAYPAL_API_URL'] ?? '';
		if ( !$clientId || !$secret || !$baseUri ) {
			throw new \LogicException( "You must put PAYPAL_API_URL, PAYPAL_API_CLIENT_ID and " .
				"PAYPAL_API_CLIENT_SECRET in the .env.* file" );
		}
		$factory->setPayPalAPI( new GuzzlePaypalAPI(
			new Client( [ 'base_uri' => $baseUri ] ),
			$clientId,
			$secret,
			$this->createStreamLoggerForPayment( 'paypal_api_errors', $factory->getLoggingPath() )
		) );
	}
}
