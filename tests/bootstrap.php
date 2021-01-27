<?php

declare( strict_types = 1 );

use Symfony\Component\Dotenv\Dotenv;

if ( PHP_SAPI !== 'cli' ) {
	die( 'Not an entry point' );
}

error_reporting( -1 );
ini_set( 'display_errors', '1' );

if ( !is_readable( __DIR__ . '/../vendor/autoload.php' ) ) {
	die( 'You need to install this package with Composer before you can run the tests' );
}

require __DIR__ . '/../vendor/autoload.php';

if ( file_exists( dirname( __DIR__ ) . '/config/bootstrap.php' ) ) {
	require dirname( __DIR__ ) . '/config/bootstrap.php';
} elseif ( method_exists( Dotenv::class, 'bootEnv' ) ) {
	// TODO investigate why we can't use '.env'. Using 'env.test' is cheating and goes against the purpose of Dotenv
	( new Dotenv() )->bootEnv( dirname( __DIR__ ) . '/.env.test' );
}
