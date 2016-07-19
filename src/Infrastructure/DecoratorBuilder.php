<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DecoratorBuilder {

	private $objectToDecorate;
	private $before;
	private $after;

	public function __construct( $objectToDecorate ) {
		$this->objectToDecorate = $objectToDecorate;
	}

	public function withBefore( callable $before ): self {
		$this->before = $before;
		return $this;
	}

	public function withAfter( callable $after ): self {
		$this->after = $after;
		return $this;
	}

	public function newDecorator() {
		$decorator = $this->newMock( $this->getDecoratedType() );

		foreach ( $this->getMethodNames() as $methodName ) {
			$decorator->method( $methodName )->willReturnCallback(
				function() use ( $methodName ) {
					call_user_func_array( $this->before, func_get_args() );
					$returnValue = call_user_func_array( [ $this->objectToDecorate, $methodName ], func_get_args() );
					call_user_func_array( $this->after, func_get_args() );

					return $returnValue;
				}
			);
		}

		$this->assertTypeRetained( $decorator );

		return $decorator;
	}

	private function getDecoratedType(): string {
		return get_class( $this->objectToDecorate );
	}

	private function newMock( string $type ): \PHPUnit_Framework_MockObject_MockObject {
		$mockBuilder = new \PHPUnit_Framework_MockObject_MockBuilder(
			new class() extends \PHPUnit_Framework_TestCase {},
			$type
		);

		$mockBuilder->disableOriginalConstructor();

		return $mockBuilder->getMock();
	}

	private function getMethodNames(): array {
		return array_filter(
			get_class_methods( $this->getDecoratedType() ),
			function( string $methodName ) {
				return $methodName !== '__construct';
			}
		);
	}

	private function assertTypeRetained( $decorator ) {
		$expectedType = $this->getDecoratedType();

		if ( !( $decorator instanceof $expectedType ) ) {
			throw new \LogicException( 'Decorator not of the correct type' );
		}
	}

}
