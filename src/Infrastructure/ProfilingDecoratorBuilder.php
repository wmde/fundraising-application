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
				$this->stopwatch->start( $profilingLabel );
			} )
			->withAfter( function () use ( $profilingLabel ) {
				$this->stopwatch->stop( $profilingLabel );

				$this->dataCollector->addCall(
					$profilingLabel,
					$this->getCallingFunctionName(),
					func_get_args()
				);
			} )
			->newDecorator();
	}

	private function getCallingFunctionName(): string {
		// TODO: this seems hardly robust! (ie will break when the PHPUnit part of the stack changes)
		$trace = debug_backtrace();
		return explode( '_', $trace[7]['class'] )[1] . '::' . $trace[7]['function'];
	}

}
