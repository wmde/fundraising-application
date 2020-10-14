<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel {

	use MicroKernelTrait;

	protected function configureContainer(ContainerConfigurator $container): void
	{
		$container->import( $this->getProjectDir(). '/config/packages/framework.yaml' );
		$container->import( $this->getProjectDir(). '/config/services.yml' );
	}

	protected function configureRoutes(RoutingConfigurator $routes): void
	{
		$routes->import( $this->getProjectDir() . '/config/routes.yml' );
	}
}
