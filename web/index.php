<?php

declare( strict_types = 1 );

stream_wrapper_unregister( 'phar' );

require_once __DIR__ . '/../vendor/autoload.php';

use FileFetcher\SimpleFileFetcher;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use WMDE\Fundraising\Frontend\App\UrlGeneratorAdapter;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\ConfigReader;
use WMDE\Fundraising\Frontend\Infrastructure\EnvironmentBootstrapper;

/**
 * @var FunFunFactory $ffFactory
 */
$ffFactory = call_user_func( function() {
    $environment = getenv( 'APP_ENV' ) ?: 'dev';

	$configReader = new ConfigReader(
		new SimpleFileFetcher(),
		...EnvironmentBootstrapper::getConfigurationPathsForEnvironment( $environment, __DIR__ . '/../app/config' )
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
