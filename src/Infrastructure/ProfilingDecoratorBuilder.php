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

	public function __construct( Stopwatch $stopwatch ) {
		$this->stopwatch = $stopwatch;
	}

	public function decorate( $objectToDecorate, string $profilingLabel ) {
		return ( new DecoratorBuilder( $objectToDecorate ) )
			->withBefore( function () use ( $profilingLabel ) {
				$this->stopwatch->start( $profilingLabel );
			} )
			->withAfter( function () use ( $profilingLabel ) {
				$this->stopwatch->stop( $profilingLabel );
			} )
			->newDecorator();
	}

}
