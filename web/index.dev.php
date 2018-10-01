<?php

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

declare( strict_types = 1 );

error_reporting( E_ALL | E_STRICT );
ini_set( 'display_errors', '1' );

require_once __DIR__ . '/../vendor/autoload.php';

use FileFetcher\SimpleFileFetcher;
use WMDE\Fundraising\Frontend\App\UrlGeneratorAdapter;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\ConfigReader;
use WMDE\Fundraising\Frontend\Infrastructure\EnvironmentBootstrapper;

/**
 * @var \WMDE\Fundraising\Frontend\Factories\FunFunFactory $ffFactory
 */
$ffFactory = call_user_func( function() {
	$environmentName = getenv( 'APP_ENV' ) ?: 'dev';

	$configReader = new ConfigReader(
		new SimpleFileFetcher(),
		...EnvironmentBootstrapper::getConfigurationPathsForEnvironment( $environmentName, __DIR__ . '/../app/config' )
	);

	$config = $configReader->getConfig();

	if ( !$config['enable-dev-entry-point'] ) {
		die( 'Dev entry point not available! Set enable-dev-entry-point to true to enable.' );
	}

	$factory = new FunFunFactory( $configReader->getConfig() );

	$environmentSetup = EnvironmentBootstrapper::getEnvironmentSetupInstance( $environmentName );
	$environmentSetup->setEnvironmentDependentInstances( $factory );

	return $factory;
} );

$app = \WMDE\Fundraising\Frontend\App\Bootstrap::initializeApplication( $ffFactory );
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
