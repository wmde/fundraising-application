<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit\Factories;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Factories\EnvironmentDependentConfigReaderFactory;

#[CoversClass( EnvironmentDependentConfigReaderFactory::class )]
class EnvironmentDependentConfigReaderFactoryTest extends TestCase {
	private const CONFIGDIR = 'config';

	public function testGivenEnvironmentName_filePathsAreReturned(): void {
		$path = vfsStream::setup( self::CONFIGDIR, null, [
			'config.dev.json' => '{}',
			'config.dist.json' => '{}'
		] );
		$factory = new EnvironmentDependentConfigReaderFactory( 'dev' );

		$this->assertSame(
			[
				vfsStream::url( self::CONFIGDIR . '/config.dist.json' ),
				vfsStream::url( self::CONFIGDIR . '/config.dev.json' )
			],
			$factory->getConfigurationPathsForEnvironment( $path->url() )
		);
	}

	public function testGivenMissingDistFile_exceptionIsThrown(): void {
		$path = vfsStream::setup( self::CONFIGDIR, null, [
			'config.dev.json' => '{}'
		] );

		$this->expectExceptionMessageMatches( '/dist/' );

		( new EnvironmentDependentConfigReaderFactory( 'dev' ) )->getConfigurationPathsForEnvironment( $path->url() );
	}

	public function testGivenMissingEnvironmentFile_exceptionIsThrown(): void {
		$path = vfsStream::setup( self::CONFIGDIR, null, [
			'config.dist.json' => '{}'
		] );

		$this->expectExceptionMessageMatches( '/dev/' );

		( new EnvironmentDependentConfigReaderFactory( 'dev' ) )->getConfigurationPathsForEnvironment( $path->url() );
	}
}
