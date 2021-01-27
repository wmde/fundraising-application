<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\DevelopmentEnvironmentSetup;
use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\EnvironmentSetupException;
use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\ProductionEnvironmentSetup;
use WMDE\Fundraising\Frontend\Infrastructure\EnvironmentBootstrapper;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeEnvironmentSetup;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\EnvironmentBootstrapper
 */
class EnvironmentBootstrapperTest extends TestCase {

	public function testGivenDefaultEnvironmentName_environmentSetupClassIsReturned(): void {
		$this->assertInstanceOf(
			DevelopmentEnvironmentSetup::class,
			( new EnvironmentBootstrapper( 'dev' ) )->getEnvironmentSetupInstance()
		);
		$this->assertInstanceOf(
			ProductionEnvironmentSetup::class,
			( new EnvironmentBootstrapper( 'uat' ) )->getEnvironmentSetupInstance()
		);
		$this->assertInstanceOf(
			ProductionEnvironmentSetup::class,
			( new EnvironmentBootstrapper( 'prod' ) )->getEnvironmentSetupInstance()
		);
	}

	public function testGivenCustomEnvironmentNameAndMap_environmentSetupClassIsReturned(): void {
		$bootstrapper = new EnvironmentBootstrapper(
			'unusual',
			[ 'unusual' => FakeEnvironmentSetup::class ]
		);
		$this->assertInstanceOf(
			FakeEnvironmentSetup::class,
			$bootstrapper->getEnvironmentSetupInstance()
		);
	}

	public function testGivenCustomEnvironmentMap_defaultEnvironmentSetupClassIsOverwritten(): void {
		$bootstrapper = new EnvironmentBootstrapper(
			'dev',
			[ 'dev' => FakeEnvironmentSetup::class ]
		);
		$this->assertInstanceOf(
			FakeEnvironmentSetup::class,
			$bootstrapper->getEnvironmentSetupInstance()
		);
	}

	public function testGivenUnknownEnvironmentName_exceptionIsThrown(): void {
		$this->expectException( EnvironmentSetupException::class );
		( new EnvironmentBootstrapper( 'unfriendly' ) )->getEnvironmentSetupInstance();
	}
}
