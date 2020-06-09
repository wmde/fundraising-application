<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Factories\EnvironmentSetup;

use PHPUnit\Framework\MockObject\MockObject;
use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\ProductionEnvironmentSetup;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * @covers \WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\ProductionEnvironmentSetup
 */
class ProductionEnvironmentSetupTest extends TestCase {
	public function testEnvironmentSetsUpEnvironmentDependentServices() {
		$expectedSetters = [
			'setLogger',
			'setPaypalLogger',
			'setSofortLogger',
			'setSofortLogger',
			'enableCaching',
			'setDoctrineConfiguration',
		];
		$supportingGetters = [ 'getLoggingPath', 'getWritableApplicationDataPath' ];
		/** @var FunFunFactory&MockObject $factory */
		$factory = $this->createMock( FunFunFactory::class );
		foreach ( $expectedSetters as $setterName ) {
			$factory->expects( $this->once() )->method( $setterName );
		}
		$methodNameMatcher = '/^(?:' . implode( '|', array_merge( $expectedSetters, $supportingGetters ) ) . ')$/';
		$factory->expects( $this->never() )->method( $this->logicalNot( $this->matchesRegularExpression( $methodNameMatcher ) ) );

		$setup = new ProductionEnvironmentSetup();
		$setup->setEnvironmentDependentInstances( $factory, [ 'logging' => [
			'handlers' => [
				[ 'method' => 'error_log', 'level' => 0 ]
			]
		] ] );
	}
}
