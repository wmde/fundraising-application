<?php
/**
 * This is a CLI configuration file for Doctrine
 * https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/reference/tools.html
 */

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Symfony\Component\Dotenv\Dotenv;
use WMDE\Fundraising\Frontend\App\Kernel;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

require __DIR__ . '/vendor/autoload.php';

if ( !class_exists( Dotenv::class ) ) {
	throw new LogicException( 'You need to add "symfony/framework-bundle" and "symfony/dotenv" as Composer dependencies.' );
}

( new Dotenv() )->bootEnv( __DIR__ . '/.env' );

$kernel = new Kernel( $_SERVER['APP_ENV'], false );
$kernel->boot();
$factory = $kernel->getContainer()->get( FunFunFactory::class );

return ConsoleRunner::createHelperSet( $factory->getEntityManager() );
