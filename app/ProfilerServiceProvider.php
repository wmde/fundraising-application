<?php

namespace WMDE\Fundraising\Frontend\App;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ProfilerServiceProvider implements ServiceProviderInterface {

	private $collector;

	public function __construct( DataCollectorInterface $collector ) {
		$this->collector = $collector;
	}

	public function register(Application $app)
	{
		$dataCollectors = $app['data_collectors'];

		$dataCollectors['funprofiler'] = $app->share( function ( $app ) {
			return $this->collector;
		});

		$app['data_collectors'] = $dataCollectors;

		$dataCollectorTemplates = $app['data_collector.templates'];
		$dataCollectorTemplates[] = array('db', '@FunProfiler/Profiler.twig');
		$app['data_collector.templates'] = $dataCollectorTemplates;

		$app['twig.loader.filesystem'] = $app->share($app->extend('twig.loader.filesystem', function ($loader) {
			/** @var \Twig_Loader_Filesystem $loader */
			$loader->addPath(dirname(__DIR__).'/app/templates', 'FunProfiler');
			return $loader;
		}));
	}

	public function boot(Application $app)
	{
	}
}