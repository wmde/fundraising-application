<?php

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

$app->run();