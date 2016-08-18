<?php


namespace Unit\Application;

use FileFetcher\FileFetcher;
use WMDE\Fundraising\Frontend\Cli\ConfigValidation\ConfigValidationException;
use WMDE\Fundraising\Frontend\Cli\ConfigValidation\SchemaLoader;

/**
 * @covers WMDE\Fundraising\Frontend\Cli\ConfigValidation\SchemaLoader
 */
class SchemaLoaderTest extends \PHPUnit_Framework_TestCase {

	public function testOnFileFetchingError_runtimeExceptionIsThrown() {
		$fileFetcher = $this->createMock( FileFetcher::class );
		$fileFetcher->method( 'fetchFile' )->willThrowException( new \RuntimeException() );
		$loader = new SchemaLoader( $fileFetcher );
		$this->expectException( \RuntimeException::class );
		$loader->loadSchema( 'test.json' );
	}

	public function testGivenInvalidJson_validationExceptionIsThrown() {
		$fileFetcher = $this->createMock( FileFetcher::class );
		$fileFetcher->method( 'fetchFile' )->willReturn( 'Not a valid JSON string' );
		$loader = new SchemaLoader( $fileFetcher );
		$this->expectException( ConfigValidationException::class );
		$loader->loadSchema( 'test.json' );
	}

	public function testGivenJsonRootIsNotAnObject_validationExceptionIsThrown() {
		$fileFetcher = $this->createMock( FileFetcher::class );
		$fileFetcher->method( 'fetchFile' )->willReturn( '"A valid JSON string"' );
		$loader = new SchemaLoader( $fileFetcher );
		$this->expectException( ConfigValidationException::class );
		$loader->loadSchema( 'test.json' );
	}

	public function testGivenValidJson_itIsReturnedAsObject() {
		$fileFetcher = $this->createMock( FileFetcher::class );
		$fileFetcher->method( 'fetchFile' )->willReturn( '{"testProperty": "A valid JSON string"}' );
		$loader = new SchemaLoader( $fileFetcher );

		$expectedObject = new \stdClass();
		$expectedObject->testProperty = 'A valid JSON string';
		$this->assertEquals( $expectedObject, $loader->loadSchema( 'test.json' ) );
	}

}
