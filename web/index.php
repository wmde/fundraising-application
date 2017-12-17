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

	$configReader = new ConfigReader(
		new \FileFetcher\SimpleFileFetcher(),
		__DIR__ . '/../app/config/config.dist.json',
		is_readable( $prodConfigPath ) ? $prodConfigPath : null
	);

	return new FunFunFactory( $configReader->getConfig() );
} );

$ffFactory->enablePageCache();

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

/**
 * @var \Silex\Application $app
 */
$app = require __DIR__ . '/../app/bootstrap.php';

$ffFactory->setSkinTwigEnvironment( $app['twig'] );

$ffFactory->setUrlGenerator( new UrlGeneratorAdapter( $app['url_generator'] ) );

$app->run();
