<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use FileFetcher\InMemoryFileFetcher;
use FileFetcher\ThrowingFileFetcher;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use WMDE\Fundraising\Frontend\FeatureToggle\Feature;
use WMDE\Fundraising\Frontend\Infrastructure\FileFeatureReader;
use WMDE\Fundraising\Frontend\Tests\Fixtures\LoggerSpy;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\FileFeatureReader
 */
class FileFeatureReaderTest extends TestCase {
	public function testGivenErrorReadingFile_willReturnEmptyList(): void {
		$logger = new LoggerSpy();
		$reader = new FileFeatureReader( new ThrowingFileFetcher(), 'features.json', $logger );

		$features = $reader->getFeatures();

		$this->assertSame( [], $features );
		$this->assertLogLevel( $logger, LogLevel::NOTICE );
	}

	public function testGivenEmptyFile_willReturnEmptyList(): void {
		$logger = new LoggerSpy();
		$reader = new FileFeatureReader( new InMemoryFileFetcher( [ 'features.json' => '' ] ), 'features.json', $logger );

		$features = $reader->getFeatures();

		$this->assertSame( [], $features );
		$this->assertLogLevel( $logger, LogLevel::NOTICE );
	}

	public function testGivenValidInput_willReturnFeatures(): void {
		$logger = new LoggerSpy();
		$rawFeatures = [
			[
				'name' => 'more_awesome',
				'active' => true
			],
			[
				'name' => 'flowery_language',
				'active' => false
			],
		];
		$reader = new FileFeatureReader( new InMemoryFileFetcher( [ 'features.json' => json_encode( $rawFeatures ) ] ), 'features.json', $logger );

		$features = $reader->getFeatures();

		$this->assertCount( 2, $features );
		$this->assertEquals( new Feature( 'more_awesome', true ), $features[0] );
		$this->assertEquals( new Feature( 'flowery_language', false ), $features[1] );
		$logger->assertNoLoggingCallsWhereMade();
	}

	/**
	 * @dataProvider invalidJsonProvider
	 */
	public function testGivenInvalidJson_willReturnEmptyList( string $fileContents ): void {
		$logger = new LoggerSpy();
		$reader = new FileFeatureReader( new InMemoryFileFetcher( [ 'some_file.json' => $fileContents ] ), 'some_file.json', $logger );

		$this->assertSame( [], $reader->getFeatures() );
		$this->assertLogLevel( $logger, LogLevel::WARNING );
	}

	public function testGivenFeaturesWithoutNames_willBeSkipped(): void {
		$logger = new LoggerSpy();
		$featuresStr = json_encode( [
			[ 'active' => false ],
			[ 'name' => 'inactive_feature' ]
		] );
		$reader = new FileFeatureReader( new InMemoryFileFetcher( [ 'some_file.json' => $featuresStr ] ), 'some_file.json', $logger );

		$features = $reader->getFeatures();

		$this->assertCount( 1, $features );
		$this->assertEquals( new Feature( 'inactive_feature', false ), $features[0] );
		$this->assertLogLevel( $logger, LogLevel::WARNING );
	}

	/**
	 * @return iterable<string, array{string}>
	 */
	public static function invalidJsonProvider(): iterable {
		yield 'broken JSON' => [ '"String with missing end quote' ];
		yield 'string' => [ '""' ];
		yield 'object' => [ '{"foo":"bar"}' ];
		yield 'boolean' => [ 'true' ];
		yield 'null' => [ 'null' ];
		yield 'array with non-objects' => [ json_encode( [ [ 'a' => 'b' ], 1, null ], JSON_THROW_ON_ERROR ) ];
	}

	private function assertLogLevel( LoggerSpy $logger, $expectedLevel ): void {
		$logEntry = $logger->getFirstLogCall();
		$this->assertNotNull( $logEntry, 'Logger should contain log message' );
		$this->assertSame( $expectedLevel, $logEntry->getLevel() );
	}

}
