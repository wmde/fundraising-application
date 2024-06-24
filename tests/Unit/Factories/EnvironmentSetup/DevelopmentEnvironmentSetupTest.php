<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Factories\EnvironmentSetup;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\DevelopmentEnvironmentSetup;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

#[CoversClass( DevelopmentEnvironmentSetup::class )]
class DevelopmentEnvironmentSetupTest extends TestCase {
	public function testEnvironmentSetsUpEnvironmentDependentServices(): void {
		$expectedSetters = [
			'setPaypalLogger',
			'setSofortLogger',
			'setDoctrineConfiguration',
			'setInternalErrorHtmlPresenter',
			'setPayPalAPI',
			'setMembershipImpressionCounter',
		];
		$supportingGetters = [
			'getLogger',
			'getLoggingPath',
			'getDoctrineXMLMappingPaths',
			'getConnection'
		];
		/** @var FunFunFactory&MockObject $factory */
		$factory = $this->createMock( FunFunFactory::class );
		foreach ( $expectedSetters as $setterName ) {
			$factory->expects( $this->once() )->method( $setterName );
		}
		$methodNameMatcher = '/^(?:' . implode( '|', array_merge( $expectedSetters, $supportingGetters ) ) . ')$/';
		$factory->expects( $this->never() )->method( $this->logicalNot( $this->matchesRegularExpression( $methodNameMatcher ) ) );

		$_ENV['PAYPAL_API_CLIENT_ID'] = 'not_a_real_client_id';
		$_ENV['PAYPAL_API_URL'] = 'https://example.com';
		$_ENV['PAYPAL_API_CLIENT_SECRET'] = 'not_a_real_client_secret';

		$setup = new DevelopmentEnvironmentSetup();
		$setup->setEnvironmentDependentInstances( $factory );
	}
}
