<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Factories\EnvironmentSetup;

use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\DevelopmentEnvironmentSetup;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * @covers \WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\DevelopmentEnvironmentSetup
 */
class DevelopmentEnvironmentSetupTest extends TestCase {
	public function testEnvironmentSetsUpLogging() {
		$expectedSetters = [
			'setLogger',
			'setPaypalLogger',
			'setSofortLogger',
			'setSofortLogger',
		];
		$supportingGetters = [ 'getLoggingPath' ];
		$factory = $this->createMock( FunFunFactory::class );
		foreach ( $expectedSetters as $setterName ) {
			$factory->expects( $this->once() )->method( $setterName );
		}
		$methodNameMatcher = '/^(?:' . implode( '|', array_merge( $expectedSetters, $supportingGetters ) ) . ')$/';
		$factory->expects( $this->never() )->method( $this->logicalNot( $this->matchesRegularExpression( $methodNameMatcher ) ) );

		$setup = new DevelopmentEnvironmentSetup();
		$setup->setEnvironmentDependentInstances( $factory, [ 'logging' => [ 'type' => 'error_log', 'level' => 0 ] ] );
	}
}
