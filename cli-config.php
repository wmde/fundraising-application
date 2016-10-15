<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use WMDE\Fundraising\Store\Factory;
use WMDE\Fundraising\Frontend\Infrastructure\ConfigReader;

$prodConfigPath = __DIR__ . '/app/config/config.prod.json';

$configReader = new ConfigReader(
	new \FileFetcher\SimpleFileFetcher(),
	__DIR__ . '/app/config/config.dist.json',
	is_readable( $prodConfigPath ) ? $prodConfigPath : ''
);

$factory = new Factory( DriverManager::getConnection( $configReader->getConfig()['db'] ) );

return ConsoleRunner::createHelperSet( $factory->getEntityManager() );
