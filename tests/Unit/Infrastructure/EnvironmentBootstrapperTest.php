<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\DevelopmentEnvironmentSetup;
use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\EnvironmentSetup;
use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\EnvironmentSetupException;
use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\ProductionEnvironmentSetup;
use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\TestEnvironmentSetup;
use WMDE\Fundraising\Frontend\Infrastructure\EnvironmentBootstrapper;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeEnvironmentSetup;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\EnvironmentBootstrapper
 */
class EnvironmentBootstrapperTest extends TestCase {
	private const CONFIGDIR = 'config';

	public function testGivenEnvironmentName_filePathsAreReturned() {
		$path = vfsStream::setup( self::CONFIGDIR, null, [
			'config.dev.json' => '{}',
			'config.dist.json' => '{}'
		] );

		$bootstrapper = new EnvironmentBootstrapper( 'dev' );
		$this->assertSame(
			[
				vfsStream::url( self::CONFIGDIR . '/config.dist.json' ),
				vfsStream::url( self::CONFIGDIR . '/config.dev.json' )
			],
			$bootstrapper->getConfigurationPathsForEnvironment( $path->url() )
		);
	}

	public function testGivenMissingDistFile_exceptionIsThrown() {
		$path = vfsStream::setup( self::CONFIGDIR, null, [
			'config.dev.json' => '{}'
		] );
		$this->expectExceptionMessageRegExp( '/dist/' );
		( new EnvironmentBootstrapper( 'dev' ) )->getConfigurationPathsForEnvironment( $path->url() );
	}

	public function testGivenMissingEnvironmentFile_exceptionIsThrown() {
		$path = vfsStream::setup( self::CONFIGDIR, null, [
			'config.dist.json' => '{}'
		] );
		$this->expectExceptionMessageRegExp( '/dev/' );
		( new EnvironmentBootstrapper( 'dev' ) )->getConfigurationPathsForEnvironment( $path->url() );
	}

	public function testGivenDefaultEnvironmentName_environmentSetupClassIsReturned() {
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

	public function testGivenCustomEnvironmentNameAndMap_environmentSetupClassIsReturned() {
		$bootstrapper = new EnvironmentBootstrapper(
			'unusual',
			[ 'unusual' => FakeEnvironmentSetup::class ]
		);
		$this->assertInstanceOf(
			FakeEnvironmentSetup::class,
			$bootstrapper->getEnvironmentSetupInstance()
		);
	}

	public function testGivenCustomEnvironmentdMap_defaultEnvironmentSetupClassIsOverwritten() {
		$bootstrapper = new EnvironmentBootstrapper(
			'dev',
			[ 'dev' => FakeEnvironmentSetup::class ]
		);
		$this->assertInstanceOf(
			FakeEnvironmentSetup::class,
			$bootstrapper->getEnvironmentSetupInstance()
		);
	}

	public function testGivenUnknownEnvironmentName_exceptionIsThrown() {
		$this->expectException( EnvironmentSetupException::class );
		( new EnvironmentBootstrapper( 'unfriendly' ) )->getEnvironmentSetupInstance();
	}
}
