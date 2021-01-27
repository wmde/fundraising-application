<?php

require_once __DIR__ . '/vendor/autoload.php';

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use FileFetcher\SimpleFileFetcher;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\EnvironmentBootstrapper;
use WMDE\Fundraising\Frontend\Infrastructure\ConfigReader;

/**
 * This is a CLI configuration file for Doctrine
 * https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/reference/tools.html
 */

$dotenv = Dotenv\Dotenv::createImmutable( __DIR__ );
$dotenv->load();

$bootstrapper = new EnvironmentBootstrapper( $_ENV['APP_ENV'] ?? 'dev' );
$factory = $bootstrapper->newFunFunFactory();

return ConsoleRunner::createHelperSet( $factory->getEntityManager() );
