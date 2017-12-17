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
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use WMDE\Fundraising\Frontend\App\UrlGeneratorAdapter;

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

	$config = $configReader->getConfig();

	if ( !$config['enable-dev-entry-point'] ) {
		die( 'Dev entry point not available! Set enable-dev-entry-point to true to enable.' );
	}

	return new \WMDE\Fundraising\Frontend\Factories\FunFunFactory( $config );
} );

$ffFactory->setLogger( call_user_func( function() use ( $ffFactory ) {
	$logger = new Logger( 'index_dev_php' );

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
$app['track_all_the_memory'] = $ffFactory;

$app->register( new Silex\Provider\HttpFragmentServiceProvider() );
$app->register( new Silex\Provider\ServiceControllerServiceProvider() );
$app->register( new Silex\Provider\TwigServiceProvider() );

$app->register( new Silex\Provider\DoctrineServiceProvider() );

$app['db'] = $ffFactory->getConnection();
$app['dbs'] = function ( $app ) {
	$app['dbs.options.initializer']();
	return [ 'default' => $app['db'] ];
};

$ffFactory->setSkinTwigEnvironment( $app['twig'] );

$ffFactory->setUrlGenerator( new UrlGeneratorAdapter( $app['url_generator'] ) );

$app->run();
