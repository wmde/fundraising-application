<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel {

	use MicroKernelTrait;

	protected function configureContainer( ContainerConfigurator $container ): void {
		$container->import( '../config/{packages}/*.yaml' );
		$container->import( '../config/{packages}/' . $this->environment . '/*.yaml' );

		if ( is_file( \dirname( __DIR__ ) . '/config/services.yaml' ) ) {
			$container->import( '../config/services.yaml' );
			$container->import( '../config/{services}_' . $this->environment . '.yaml' );
		}
	}

	protected function configureRoutes( RoutingConfigurator $routes ): void {
		$routes->import( '../config/routes.yaml' );
		$routes->import( '../config/api-routes.yaml' );
	}

	public function getCacheDir(): string {
		// Use /tmp in Docker images, because it's world-writable
		if ( $this->environment === 'dev' || $this->environment === 'test' ) {
			return "/tmp/symfony/cache/{$this->environment}";
		}
		return parent::getCacheDir();
	}

	public function getLogDir(): string {
		// Use /tmp in Docker images, because it's world-writable
		if ( $this->environment === 'dev' || $this->environment === 'test' ) {
			return "/tmp/symfony/log/{$this->environment}";
		}
		return parent::getLogDir();
	}

}
