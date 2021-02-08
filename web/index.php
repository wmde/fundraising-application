<?php

declare( strict_types = 1 );

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\Frontend\App\Kernel;

require_once __DIR__ . '/../vendor/autoload.php';

// We set the env from the .env file instead of web server settings because we want to be independent from the hosting provider
( new Dotenv() )->bootEnv( __DIR__ . '/../.env' );

$env = $_SERVER['APP_ENV'] ?? 'dev';
$debug = $_SERVER['APP_DEBUG'] ? (bool)$_SERVER['APP_DEBUG'] : false;

if ( $debug ) {
	umask( 0000 );
	Debug::enable();
}

$kernel = new Kernel( $env, $debug );
$request = Request::createFromGlobals();
$response = $kernel->handle( $request );
$response->send();
$kernel->terminate( $request, $response );

