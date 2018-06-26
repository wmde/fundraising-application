<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Cli;

use FileFetcher\FileFetcher;
use WMDE\Fundraising\Frontend\Cli\ApplicationConfigValidation\ConfigValidationException;
use WMDE\Fundraising\Frontend\Cli\ApplicationConfigValidation\SchemaLoader;

/**
 * @covers \WMDE\Fundraising\Frontend\Cli\ApplicationConfigValidation\SchemaLoader
 */
class SchemaLoaderTest extends \PHPUnit\Framework\TestCase {

	public function testOnFileFetchingError_runtimeExceptionIsThrown(): void {
		$fileFetcher = $this->createMock( FileFetcher::class );
		$fileFetcher->method( 'fetchFile' )->willThrowException( new \RuntimeException() );
		$loader = new SchemaLoader( $fileFetcher );
		$this->expectException( \RuntimeException::class );
		$loader->loadSchema( 'test.json' );
	}

	public function testGivenInvalidJson_validationExceptionIsThrown(): void {
		$fileFetcher = $this->createMock( FileFetcher::class );
		$fileFetcher->method( 'fetchFile' )->willReturn( 'Not a valid JSON string' );
		$loader = new SchemaLoader( $fileFetcher );
		$this->expectException( ConfigValidationException::class );
		$loader->loadSchema( 'test.json' );
	}

	public function testGivenJsonRootIsNotAnObject_validationExceptionIsThrown(): void {
		$fileFetcher = $this->createMock( FileFetcher::class );
		$fileFetcher->method( 'fetchFile' )->willReturn( '"A valid JSON string"' );
		$loader = new SchemaLoader( $fileFetcher );
		$this->expectException( ConfigValidationException::class );
		$loader->loadSchema( 'test.json' );
	}

	public function testGivenValidJson_itIsReturnedAsObject(): void {
		$fileFetcher = $this->createMock( FileFetcher::class );
		$fileFetcher->method( 'fetchFile' )->willReturn( '{"testProperty": "A valid JSON string"}' );
		$loader = new SchemaLoader( $fileFetcher );

		$expectedObject = new \stdClass();
		$expectedObject->testProperty = 'A valid JSON string';
		$this->assertEquals( $expectedObject, $loader->loadSchema( 'test.json' ) );
	}

}
