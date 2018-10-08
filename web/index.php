<?php

declare( strict_types = 1 );

stream_wrapper_unregister( 'phar' );

require_once __DIR__ . '/../vendor/autoload.php';
use FileFetcher\SimpleFileFetcher;
use WMDE\Fundraising\Frontend\App\UrlGeneratorAdapter;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\ConfigReader;
use WMDE\Fundraising\Frontend\Infrastructure\EnvironmentBootstrapper;

/**
 * @var FunFunFactory $ffFactory
 */
$ffFactory = call_user_func( function() {
    $bootstrapper = new EnvironmentBootstrapper( getenv( 'APP_ENV' ) ?: 'dev' );

	$configReader = new ConfigReader(
		new SimpleFileFetcher(),
		...$bootstrapper->getConfigurationPathsForEnvironment( __DIR__ . '/../app/config' )
	);

	$config = $configReader->getConfig();
	$factory = new FunFunFactory( $config );

	$bootstrapper->getEnvironmentSetupInstance()
		->setEnvironmentDependentInstances( $factory, $config );

	return $factory;
} );

$app = \WMDE\Fundraising\Frontend\App\Bootstrap::initializeApplication( $ffFactory );

$ffFactory->setSkinTwigEnvironment( $app['twig'] );

$ffFactory->setUrlGenerator( new UrlGeneratorAdapter( $app['url_generator'] ) );

$app->run();
