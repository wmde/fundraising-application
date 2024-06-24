<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Cli;

use FileFetcher\FileFetcher;
use FileFetcher\StubFileFetcher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Cli\ApplicationConfigValidation\ConfigValidationException;
use WMDE\Fundraising\Frontend\Cli\ApplicationConfigValidation\SchemaLoader;

#[CoversClass( SchemaLoader::class )]
class SchemaLoaderTest extends TestCase {

	public function testOnFileFetchingError_runtimeExceptionIsThrown(): void {
		/** @var FileFetcher&MockObject $fileFetcher */
		$fileFetcher = $this->createMock( FileFetcher::class );
		$fileFetcher->method( 'fetchFile' )->willThrowException( new \RuntimeException() );
		$loader = new SchemaLoader( $fileFetcher );
		$this->expectException( \RuntimeException::class );
		$loader->loadSchema( 'test.json' );
	}

	public function testGivenInvalidJson_validationExceptionIsThrown(): void {
		$fileFetcher = new StubFileFetcher( 'Not a valid JSON string' );
		$loader = new SchemaLoader( $fileFetcher );
		$this->expectException( ConfigValidationException::class );
		$loader->loadSchema( 'test.json' );
	}

	public function testGivenJsonRootIsNotAnObject_validationExceptionIsThrown(): void {
		$fileFetcher = new StubFileFetcher( '"A valid JSON string"' );
		$loader = new SchemaLoader( $fileFetcher );
		$this->expectException( ConfigValidationException::class );
		$loader->loadSchema( 'test.json' );
	}

	public function testGivenValidJson_itIsReturnedAsObject(): void {
		$fileFetcher = new StubFileFetcher( '{"testProperty": "A valid JSON string"}' );
		$loader = new SchemaLoader( $fileFetcher );

		$expectedObject = new \stdClass();
		$expectedObject->testProperty = 'A valid JSON string';
		$this->assertEquals( $expectedObject, $loader->loadSchema( 'test.json' ) );
	}

}
