<?php

declare( strict_types = 1 );

stream_wrapper_unregister( 'phar' );

require_once __DIR__ . '/../vendor/autoload.php';

use WMDE\Fundraising\Frontend\App\UrlGeneratorAdapter;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

$dotenv = Dotenv\Dotenv::createImmutable( __DIR__ . '/..' );
$dotenv->load();

$ffFactory = FunFunFactory::getInstanceForEnvironment( $_ENV['APP_ENV'] ?? 'dev' );

$app = \WMDE\Fundraising\Frontend\App\Bootstrap::initializeApplication( $ffFactory );

$ffFactory->setSkinTwigEnvironment( $app['twig'] );

$ffFactory->setUrlGenerator( new UrlGeneratorAdapter( $app['url_generator'] ) );

$app->run();
