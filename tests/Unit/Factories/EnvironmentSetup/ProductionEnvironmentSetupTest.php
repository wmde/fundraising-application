<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Factories\EnvironmentSetup;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\ProductionEnvironmentSetup;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * @covers \WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\ProductionEnvironmentSetup
 */
class ProductionEnvironmentSetupTest extends TestCase {
	public function testEnvironmentSetsUpEnvironmentDependentServices() {
		$expectedSetters = [
			'setConfigCache',
			'setPaypalLogger',
			'setSofortLogger',
			'setCreditCardLogger',
			'setDoctrineConfiguration',
			'setPayPalAPI',
		];
		$supportingGetters = [
			'getCachePath',
			'getLogger',
			'getLoggingPath',
			'getWritableApplicationDataPath',
			'getDoctrineXMLMappingPaths',
		];
		/** @var FunFunFactory&MockObject $factory */
		$factory = $this->createMock( FunFunFactory::class );
		foreach ( $expectedSetters as $methodName ) {
			$factory->expects( $this->once() )->method( $methodName );
		}
		$methodNameMatcher = '/^(?:' . implode( '|', array_merge( $expectedSetters, $supportingGetters ) ) . ')$/';
		$factory->expects( $this->never() )->method( $this->logicalNot( $this->matchesRegularExpression( $methodNameMatcher ) ) );

		$_ENV['PAYPAL_API_CLIENT_ID'] = 'not_a_real_client_id';
		$_ENV['PAYPAL_API_URL'] = 'https://example.com';
		$_ENV['PAYPAL_API_CLIENT_SECRET'] = 'not_a_real_client_secret';

		$setup = new ProductionEnvironmentSetup();
		$setup->setEnvironmentDependentInstances( $factory );
	}
}
