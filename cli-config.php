<?php

require_once __DIR__ . '/vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use WMDE\Fundraising\Store\Factory;
use WMDE\Fundraising\Frontend\Infrastructure\ConfigReader;

/**
 * This is a CLI configuration file for Doctrine
 * https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/tools.html
 */

$prodConfigPath = __DIR__ . '/app/config/config.prod.json';

$configReader = new ConfigReader(
	new \FileFetcher\SimpleFileFetcher(),
	__DIR__ . '/app/config/config.dist.json',
	is_readable( $prodConfigPath ) ? $prodConfigPath : ''
);

$factory = new Factory( DriverManager::getConnection( $configReader->getConfig()['db'] ) );

return ConsoleRunner::createHelperSet( $factory->getEntityManager() );
