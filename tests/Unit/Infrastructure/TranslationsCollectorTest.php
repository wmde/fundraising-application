<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Infrastructure;

use FileFetcher\InMemoryFileFetcher;
use PHPUnit\Framework\TestCase;

class TranslationsCollectorTest extends TestCase {

	public function testGivenOneFileItReturnsTheContents(): void {

		$testFile = [ 'test.json'=> '{"mail_subject_getintouch": "Sie haben gefragt, wir werden antworten"}' ];

		$translationsCollector = new TranslationsCollector( new InMemoryFileFetcher( $testFile ) );
		$translationsCollector->addTranslationFile( 'test.json' );
		$actual = $translationsCollector->collectTranslations();
		$this->assertEquals( ['mail_subject_getintouch' => 'Sie haben gefragt, wir werden antworten'], $actual );
	}

	public function testGivenTwoFilesItMergesTheirContents(): void {
		$testFile = [ 'test1.json'=> '{"mail_subject_getintouch": "I will be overwritten", "another_key": "I will stay as I am" }',
						'test2.json'=> '{"mail_subject_getintouch": "Sie haben gefragt, wir werden antworten"}'
						];

		$translationsCollector = new TranslationsCollector( new InMemoryFileFetcher( $testFile ) );
		$translationsCollector->addTranslationFile( 'test1.json' );
		$translationsCollector->addTranslationFile( 'test2.json' );
		$actual = $translationsCollector->collectTranslations();
		$this->assertEquals( ['mail_subject_getintouch' => 'Sie haben gefragt, wir werden antworten',
								'another_key' => 'I will stay as I am' ], $actual );
	}

	public function testGivenNoFileReturnsEmptyArray(): void {

		$translationsCollector = new TranslationsCollector( new InMemoryFileFetcher( [] ) );
		$actual = $translationsCollector->collectTranslations();
		$this->assertEquals( [], $actual );

	}

	/**
	 * @dataProvider invalidJSONProvider
	 */
	public function testGivenWrongJSONFormatThrowsException( string $testJSONs ): void {

		$testFile = [ 'test.json'=> $testJSONs ];

		$translationsCollector = new TranslationsCollector( new InMemoryFileFetcher( $testFile ) );
		$translationsCollector->addTranslationFile( 'test.json' );

		$this->expectException( \RuntimeException::class );
		$translationsCollector->collectTranslations();
	}


	public function invalidJSONProvider(): array {
		return [
			['"i will not work"'],
			['{'],
			['']
		];
	}
}
