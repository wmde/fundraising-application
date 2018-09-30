<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Infrastructure\EnvironmentBootstrapper;

class EnvironmentBootstrapperTest extends TestCase {
	private const CONFIGDIR = 'config';

	public function testGivenEnvironmentName_filePathsAreReturned() {
		$path = vfsStream::setup( self::CONFIGDIR, null, [
			'config.dev.json' => '{}',
			'config.dist.json' => '{}'
		] );

		$this->assertSame(
			[
				vfsStream::url( self::CONFIGDIR . '/config.dist.json' ),
				vfsStream::url( self::CONFIGDIR . '/config.dev.json' )
			],
			EnvironmentBootstrapper::getConfigurationPathsForEnvironment( 'dev', $path->url() )
		);
	}

	public function testGivenMissingDistFile_exceptionIsThrown() {
		$path = vfsStream::setup( self::CONFIGDIR, null, [
			'config.dev.json' => '{}'
		] );
		$this->expectExceptionMessageRegExp( '/dist/' );
		EnvironmentBootstrapper::getConfigurationPathsForEnvironment( 'dev', $path->url() );
	}

	public function testGivenMissingEnvironmentFile_exceptionIsThrown() {
		$path = vfsStream::setup( self::CONFIGDIR, null, [
			'config.dist.json' => '{}'
		] );
		$this->expectExceptionMessageRegExp( '/dev/' );
		EnvironmentBootstrapper::getConfigurationPathsForEnvironment( 'dev', $path->url() );
	}

}
