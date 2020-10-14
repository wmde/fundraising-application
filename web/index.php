<?php

declare( strict_types = 1 );

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\Frontend\App\Kernel;

require_once __DIR__ . '/../vendor/autoload.php';

(new Dotenv())->bootEnv( dirname( __DIR__ ) . '/.env' );

$env = $_SERVER['APP_ENV'] ?? 'dev';
$debug = $_SERVER['APP_DEBUG'] ? (bool)$_SERVER['APP_DEBUG'] : false;

if ( $debug ) {
	umask( 0000 );
	Debug::enable();
}

if ( $trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? false ) {
	Request::setTrustedProxies(
		explode( ',', $trustedProxies ),
		Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST
	);
}

if ( $trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? false ) {
	Request::setTrustedHosts( [ $trustedHosts ] );
}

$kernel = new Kernel( $env, $debug );
$request = Request::createFromGlobals();
$response = $kernel->handle( $request );
$response->send();
$kernel->terminate( $request, $response );

