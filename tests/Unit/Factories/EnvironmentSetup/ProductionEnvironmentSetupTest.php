<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Factories\EnvironmentSetup;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\ProductionEnvironmentSetup;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

#[CoversClass( ProductionEnvironmentSetup::class )]
class ProductionEnvironmentSetupTest extends TestCase {
	public function testEnvironmentSetsUpEnvironmentDependentServices(): void {
		$expectedSetters = [
			'setConfigCache',
			'setPaypalLogger',
			'setSofortLogger',
			'setCreditCardLogger',
			'setDoctrineConfiguration',
			'setPayPalAPI',
			'setMembershipImpressionCounter',
		];
		$supportingGetters = [
			'getCachePath',
			'getLogger',
			'getLoggingPath',
			'getWritableApplicationDataPath',
			'getDoctrineXMLMappingPaths',
			'getConnection'
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
