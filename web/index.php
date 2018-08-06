<?php

declare( strict_types = 1 );

require_once __DIR__ . '/../vendor/autoload.php';

use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use WMDE\Fundraising\Frontend\App\UrlGeneratorAdapter;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\ConfigReader;

/**
 * @var FunFunFactory $ffFactory
 */
$ffFactory = call_user_func( function() {
	$prodConfigPath = __DIR__ . '/../app/config/config.prod.json';
	$configPaths = [ __DIR__ . '/../app/config/config.dist.json' ];

	if ( is_readable( $prodConfigPath ) ) {
		$configPaths[] = $prodConfigPath;
	}

	$configReader = new \WMDE\Fundraising\Frontend\Infrastructure\ConfigReader(
		new \FileFetcher\SimpleFileFetcher(),
		...$configPaths
	);

	return new FunFunFactory( $configReader->getConfig() );
} );

$ffFactory->enableCaching();

$ffFactory->setLogger( call_user_func( function() use ( $ffFactory ) {
	$logger = new Logger( 'index_php' );

	$streamHandler = new StreamHandler(
		$ffFactory->getLoggingPath() . '/error-debug.log'
	);

	$fingersCrossedHandler = new FingersCrossedHandler( $streamHandler );
	$streamHandler->setFormatter( new LineFormatter( LineFormatter::SIMPLE_FORMAT ) );
	$logger->pushHandler( $fingersCrossedHandler );

	$errorHandler = new StreamHandler(
		$ffFactory->getLoggingPath() . '/error.log',
		Logger::ERROR
	);

	$errorHandler->setFormatter( new JsonFormatter() );
	$logger->pushHandler( $errorHandler );

	return $logger;
} ) );

$ffFactory->setPaypalLogger( call_user_func( function() use ( $ffFactory ) {
	$logger = new Logger( 'paypal' );

	$streamHandler = new StreamHandler(
		$ffFactory->getLoggingPath() . '/paypal.log'
	);

	$streamHandler->setFormatter( new JsonFormatter() );
	$logger->pushHandler( $streamHandler );

	return $logger;
} ) );

$ffFactory->setSofortLogger( call_user_func( function() use ( $ffFactory ) {
	$logger = new Logger( 'sofort' );

	$streamHandler = new StreamHandler( $ffFactory->getLoggingPath() . '/sofort.log' );

	$streamHandler->setFormatter( new JsonFormatter() );
	$logger->pushHandler( $streamHandler );

	return $logger;
} ) );

$app = \WMDE\Fundraising\Frontend\App\Bootstrap::initializeApplication( $ffFactory );

$ffFactory->setSkinTwigEnvironment( $app['twig'] );

$ffFactory->setUrlGenerator( new UrlGeneratorAdapter( $app['url_generator'] ) );

$app->run();
