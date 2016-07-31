<?php

declare( strict_types = 1 );

require_once __DIR__ . '/../vendor/autoload.php';

use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\ApplicationContext\Infrastructure\ConfigReader;

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
	$logger = new Logger( 'WMDE Fundraising Frontend logger' );

	$streamHandler = new StreamHandler(
		$ffFactory->getLoggingPath() . '/' . ( new \DateTime() )->format( 'Y-m-d\TH:i:s\Z' ) . '.log'
	);

	$bufferHandler = new BufferHandler( $streamHandler, 500, Logger::DEBUG, true, true );
	$streamHandler->setFormatter( new LineFormatter( "%message% - %context%\n" ) );
	$logger->pushHandler( $bufferHandler );

	$errorHandler = new StreamHandler(
		$ffFactory->getLoggingPath() . '/error.log',
		Logger::ERROR
	);

	$errorHandler->setFormatter( new JsonFormatter() );
	$logger->pushHandler( $errorHandler );

	return $logger;
} ) );

/**
 * @var \Silex\Application $app
 */
$app = require __DIR__ . '/../app/bootstrap.php';

$app->run();