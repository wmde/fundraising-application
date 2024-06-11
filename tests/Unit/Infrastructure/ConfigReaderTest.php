<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use FileFetcher\SimpleFileFetcher;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WMDE\Fundraising\Frontend\Infrastructure\ConfigReader;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\ConfigReader
 */
class ConfigReaderTest extends TestCase {

	/**
	 * @var vfsStreamDirectory
	 */
	private vfsStreamDirectory $dir;

	private string $distPath;
	private string $emptyPath;

	public function setUp(): void {
		$this->dir = vfsStream::setup( 'ConfigReaderTest' );

		$this->distPath = vfsStream::newFile( 'config.dist.json' )
			->at( $this->dir )->withContent( $this->getDistConfigContents() )->url();

		$this->emptyPath = vfsStream::newFile( 'empty.json' )
			->at( $this->dir )->withContent( '[]' )->url();
	}

	private function getDistConfigContents(): string {
		return json_encode(
			$this->getDistConfig(),
			JSON_PRETTY_PRINT
		);
	}

	/**
	 * @return array<string, string|array<string|int, string>>
	 */
	private function getDistConfig(): array {
		return [
			'db' => [
				'user' => '',
				'password' => '',
				'port' => ''
			],
			'pink' => 'fluffy',
			'unicorns' => [ 'rainbows' ]
		];
	}

	private function getInstanceConfigContents(): string {
		return json_encode(
			[
				'db' => [
					'user' => 'nyan',
					'password' => 'cat'
				],
				'unicorns' => [ 'foo', 'bar', 'baz' ]
			],
			JSON_PRETTY_PRINT
		);
	}

	public function testReadSingleConfigFile(): void {
		$reader = new ConfigReader( new SimpleFileFetcher(), $this->distPath );

		$this->assertEquals( $this->getDistConfig(), $reader->getConfig() );
	}

	public function testWhenReadingDistAndInstanceConfig_instanceGetsMergedIntoDist(): void {
		$instancePath = vfsStream::newFile( 'config.json' )
			->at( $this->dir )->withContent( $this->getInstanceConfigContents() )->url();

		$reader = new ConfigReader( new SimpleFileFetcher(), $this->distPath, $instancePath );

		$this->assertEquals(
			[
				'db' => [
					'user' => 'nyan',
					'password' => 'cat',
					'port' => ''
				],
				'pink' => 'fluffy',
				'unicorns' => [ 'foo', 'bar', 'baz' ]
			],
			$reader->getConfig()
		);
	}

	public function testWhenInstanceFileIsEmpty_distConfigIsReturned(): void {
		$reader = new ConfigReader( new SimpleFileFetcher(), $this->distPath, $this->emptyPath );

		$this->assertEquals( $this->getDistConfig(), $reader->getConfig() );
	}

	public function testWhenDistFileDoesNotExist_exceptionIsThrown(): void {
		$reader = new ConfigReader( new SimpleFileFetcher(), $this->distPath . 'foo', $this->emptyPath );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessageMatches( '/Cannot read config file at path.*/' );
		$reader->getConfig();
	}

	public function testWhenInstanceFileDoesNotExist_exceptionIsThrown(): void {
		$reader = new ConfigReader( new SimpleFileFetcher(), $this->distPath, $this->emptyPath . 'foo' );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessageMatches( '/Cannot read config file at path.*/' );
		$reader->getConfig();
	}

	public function testWhenConfigIsNotJson_exceptionIsThrown(): void {
		$notJsonPath = vfsStream::newFile( 'not.json' )->at( $this->dir )->withContent( 'kittens' )->url();

		$reader = new ConfigReader( new SimpleFileFetcher(), $notJsonPath );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessageMatches( '/No valid config data found in config file at path.*/' );
		$reader->getConfig();
	}

	public function testWhenConfigJsonIsNotArray_exceptionIsThrown(): void {
		$notArrayPath = vfsStream::newFile( 'not-array.json' )->at( $this->dir )->withContent( '42' )->url();

		$reader = new ConfigReader( new SimpleFileFetcher(), $notArrayPath );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessageMatches( '/No valid config data found in config file at path.*/' );
		$reader->getConfig();
	}

}
