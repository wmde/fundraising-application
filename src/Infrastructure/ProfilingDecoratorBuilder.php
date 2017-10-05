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

	public function decorate( $objectToDecorate, string $profilingLabel ) {	// @codingStandardsIgnoreLine
		return ( new DecoratorBuilder( $objectToDecorate ) )
			->withBefore( function () use ( $objectToDecorate, $profilingLabel ): void {
				$callingFunctionName = $this->getCallingFunctionName();

				$this->dataCollector->addCall(
					$profilingLabel,
					$this->getClassAndFunction( $objectToDecorate, $callingFunctionName ),
					$this->getFunctionArgumentsWithKeys( func_get_args(), get_class( $objectToDecorate ), $callingFunctionName )
				);

				$this->stopwatch->start( $profilingLabel );
			} )
			->withAfter( function () use ( $profilingLabel ): void {
				$this->stopwatch->stop( $profilingLabel );
			} )
			->newDecorator();
	}

	private function getClassAndFunction( $objectToDecorate, string $callingFunction ): string {	// @codingStandardsIgnoreLine
		$classNameParts = explode( '\\', get_class( $objectToDecorate ) );
		return end( $classNameParts ) . '::' . $callingFunction;
	}

	private function getCallingFunctionName(): string {
		// TODO: this seems hardly robust! (ie will break when the PHPUnit part of the stack changes)
		return debug_backtrace()[7]['function'];
	}

	private function getFunctionArgumentsWithKeys( array $arguments, string $className, string $methodName ): array {
		$reflection = new \ReflectionMethod( $className, $methodName );

		foreach ( $reflection->getParameters() as $param ) {
			if ( isset( $arguments[$param->getPosition()] ) ) {
				$arguments[$param->getName()] = $arguments[$param->getPosition()];
				unset( $arguments[$param->getPosition()] );
			}
		}

		return $arguments;
	}

}
