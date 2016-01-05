<?php

if ( PHP_SAPI !== 'cli' ) {
	die( 'Not an entry point' );
}

error_reporting( E_ALL | E_STRICT );
ini_set( 'display_errors', 1 );

if ( !is_readable( __DIR__ . '/../vendor/autoload.php' ) ) {
	die( 'You need to install this package with Composer before you can run the tests' );
}

$autoLoader = require __DIR__ . '/../vendor/autoload.php';

$autoLoader->addPsr4( 'WMDE\\Fundraising\\Frontend\\Tests\\Unit\\', __DIR__ . '/unit/' );
$autoLoader->addPsr4( 'WMDE\\Fundraising\\Frontend\\Tests\\Integration\\', __DIR__ . '/integration/' );
$autoLoader->addPsr4( 'WMDE\\Fundraising\\Frontend\\Tests\\Fixtures\\', __DIR__ . '/fixtures/' );

unset( $autoLoader );
