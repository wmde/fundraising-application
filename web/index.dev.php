<?php

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

declare( strict_types = 1 );

error_reporting( E_ALL | E_STRICT );
ini_set( 'display_errors', '1' );

require_once __DIR__ . '/../vendor/autoload.php';

use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * @var \WMDE\Fundraising\Frontend\Factories\FunFunFactory $ffFactory
 */
$ffFactory = call_user_func( function() {
	$prodConfigPath = __DIR__ . '/../app/config/config.prod.json';

	$configReader = new \WMDE\Fundraising\Frontend\Infrastructure\ConfigReader(
		new \FileFetcher\SimpleFileFetcher(),
		__DIR__ . '/../app/config/config.dist.json',
		is_readable( $prodConfigPath ) ? $prodConfigPath : null
	);

	return new \WMDE\Fundraising\Frontend\Factories\FunFunFactory( $configReader->getConfig() );
} );

$ffFactory->setLogger( call_user_func( function() use ( $ffFactory ) {
	$logger = new Logger( 'WMDE Fundraising Frontend logger' );

	$streamHandler = new StreamHandler(
		$ffFactory->getLoggingPath() . '/' . ( new \DateTime() )->format( 'Y-m-d\TH:i:s\Z' ) . '.log'
	);

	$bufferHandler = new BufferHandler( $streamHandler, 500, Logger::DEBUG, true, true );
	$streamHandler->setFormatter( new LineFormatter( "%message%\n" ) );
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
$app['track_all_the_memory'] = $ffFactory;

$app->register( new Silex\Provider\HttpFragmentServiceProvider() );
$app->register( new Silex\Provider\ServiceControllerServiceProvider() );
$app->register( new Silex\Provider\TwigServiceProvider() );
$app->register( new Silex\Provider\UrlGeneratorServiceProvider() );

$app->register( new Silex\Provider\DoctrineServiceProvider() );

$app['db'] = $ffFactory->getConnection();
$app['dbs'] = $app->share( function ( $app ) {
	$app['dbs.options.initializer']();
	return [ 'default' => $app['db'] ];
} );

$app->register(
	new Silex\Provider\WebProfilerServiceProvider(),
	[
		'profiler.cache_dir' => $ffFactory->getCachePath() . '/profiler',
		'profiler.mount_prefix' => '/_profiler',
	]
);

$app->register( new Sorien\Provider\DoctrineProfilerServiceProvider() );

$ffFactory->setProfiler( $app['stopwatch'] );

$app->run();
