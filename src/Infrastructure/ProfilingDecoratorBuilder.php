<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use GenericDecorator\DecoratorBuilder;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ProfilingDecoratorBuilder {

	private $stopwatch;
	private $dataCollector;

	public function __construct( Stopwatch $stopwatch, ProfilerDataCollector $dataCollector ) {
		$this->stopwatch = $stopwatch;
		$this->dataCollector = $dataCollector;
	}

	public function decorate( $objectToDecorate, string $profilingLabel ) {
		return ( new DecoratorBuilder( $objectToDecorate ) )
			->withBefore( function () use ( $profilingLabel ) {
				$this->dataCollector->getModifiableData()['calls'][$profilingLabel][] = func_get_args();
				$this->stopwatch->start( $profilingLabel );
			} )
			->withAfter( function () use ( $profilingLabel ) {
				$this->stopwatch->stop( $profilingLabel );
			} )
			->newDecorator();
	}

}
