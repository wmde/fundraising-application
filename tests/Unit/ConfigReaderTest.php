<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\Unit;

use FileFetcher\SimpleFileFetcher;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use RuntimeException;
use WMDE\Fundraising\Frontend\ConfigReader;

/**
 * @covers WMDE\Fundraising\Frontend\ConfigReader
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ConfigReaderTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var vfsStreamDirectory
	 */
	private $dir;

	private $distPath;
	private $emptyPath;

	public function setUp() {
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

	public function testReadSingleConfigFile() {
		$reader = new ConfigReader( new SimpleFileFetcher(), $this->distPath );

		$this->assertEquals( $this->getDistConfig(), $reader->getConfig() );
	}

	public function testWhenReadingDistAndInstanceConfig_instanceGetsMergedIntoDist() {
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

	public function testWhenInstanceFileIsEmpty_distConfigIsReturned() {
		$reader = new ConfigReader( new SimpleFileFetcher(), $this->distPath, $this->emptyPath );

		$this->assertEquals( $this->getDistConfig(), $reader->getConfig() );
	}

	public function testWhenDistFileDoesNotExist_exceptionIsThrown() {
		$reader = new ConfigReader( new SimpleFileFetcher(), $this->distPath . 'foo', $this->emptyPath );

		$this->setExpectedException( RuntimeException::class );
		$reader->getConfig();
	}

	public function testWhenInstanceFileDoesNotExist_exceptionIsThrown() {
		$reader = new ConfigReader( new SimpleFileFetcher(), $this->distPath, $this->emptyPath . 'foo' );

		$this->setExpectedException( RuntimeException::class );
		$reader->getConfig();
	}

	public function testWhenConfigIsNotJson_exceptionIsThrown() {
		$notJsonPath = vfsStream::newFile( 'not.json' )->at( $this->dir )->withContent( 'kittens' )->url();

		$reader = new ConfigReader( new SimpleFileFetcher(), $notJsonPath );

		$this->setExpectedException( RuntimeException::class );
		$reader->getConfig();
	}

	public function testWhenConfigJsonIsNotArray_exceptionIsThrown() {
		$notArrayPath = vfsStream::newFile( 'not-array.json' )->at( $this->dir )->withContent( '42' )->url();

		$reader = new ConfigReader( new SimpleFileFetcher(), $notArrayPath );

		$this->setExpectedException( RuntimeException::class );
		$reader->getConfig();
	}

}
