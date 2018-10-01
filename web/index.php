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
    $environmentName = getenv( 'APP_ENV' ) ?: 'dev';

	$configReader = new ConfigReader(
		new SimpleFileFetcher(),
		...EnvironmentBootstrapper::getConfigurationPathsForEnvironment( $environmentName, __DIR__ . '/../app/config' )
	);

	$factory = new FunFunFactory( $configReader->getConfig() );

	$environmentSetup = EnvironmentBootstrapper::getEnvironmentSetupInstance( $environmentName );
	$environmentSetup->setEnvironmentDependentInstances( $factory );

	return $factory;
} );

$app = \WMDE\Fundraising\Frontend\App\Bootstrap::initializeApplication( $ffFactory );

$ffFactory->setSkinTwigEnvironment( $app['twig'] );

$ffFactory->setUrlGenerator( new UrlGeneratorAdapter( $app['url_generator'] ) );

$app->run();
