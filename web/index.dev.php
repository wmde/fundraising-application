<?php

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

error_reporting( E_ALL | E_STRICT );
ini_set( 'display_errors', 1 );

require_once __DIR__ . '/../vendor/autoload.php';

$ffFactory = call_user_func( function() {
	$prodConfigPath = __DIR__ . '/../app/config/config.prod.json';

	$configReader = new \WMDE\Fundraising\Frontend\ConfigReader(
		new \FileFetcher\SimpleFileFetcher(),
		__DIR__ . '/../app/config/config.dist.json',
		is_readable( $prodConfigPath ) ? $prodConfigPath : null
	);

	return new \WMDE\Fundraising\Frontend\FunFunFactory( $configReader->getConfig() );
} );

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
		'profiler.cache_dir' => __DIR__ . '/../app/cache/profiler',
		'profiler.mount_prefix' => '/_profiler',
	]
);

$app->register( new Sorien\Provider\DoctrineProfilerServiceProvider() );

$app->run();