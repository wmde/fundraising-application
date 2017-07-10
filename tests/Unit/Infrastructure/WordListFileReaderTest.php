<?php

declare( strict_types = 1 );

namespace Unit\Infrastructure;

use FileFetcher\ErrorLoggingFileFetcher;
use FileFetcher\InMemoryFileFetcher;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use WMDE\Fundraising\Frontend\Infrastructure\WordListFileReader;

class WordListFileReaderTest extends TestCase {

	public function testGivenEmptyString_anEmptyListIsReturned(): void {
		$stringList = new WordListFileReader( $this->getFileFetcherWithContent( [ 'empty.txt' => '' ] ), 'empty.txt' );
		$this->assertSame( [], $stringList->toArray() );
	}

	public function testGivenEmptyFilename_anEmptyListIsReturned(): void {
		$stringList = new WordListFileReader( $this->getFileFetcherWithContent( [ 'empty.txt' => '' ] ), '' );
		$this->assertSame( [], $stringList->toArray() );
	}

	public function testGivenMissingFile_fileFetchingExceptionIsCaught(): void {
		$stringList = new WordListFileReader( $this->getFileFetcherWithContent( [] ), 'utopia.txt' );
		$this->assertSame( [], $stringList->toArray() );
	}

	public function testGivenMultilineFile_eachLineIsReturned(): void {
		$stringList = new WordListFileReader(
			$this->getFileFetcherWithContent( [ 'words.txt' => "one\ntwo\nthree\ntechno" ] ),
			'words.txt'
		);
		$this->assertSame( [ 'one', 'two', 'three', 'techno' ], $stringList->toArray() );
	}

	public function testGivenFileWithEmptyLinesAndTabs_whitespaceIsStripped(): void {
		$stringList = new WordListFileReader(
			$this->getFileFetcherWithContent( [ 'words.txt' => "one  \n\ttwo\n\t\tthree\n\n\t\t\ttechno\r\n" ] ),
			'words.txt'
		);
		$this->assertEquals( [ 'one', 'two', 'three', 'techno' ], $stringList->toArray() );
	}

	private function getFileFetcherWithContent( array $content ) {
		return new ErrorLoggingFileFetcher( new InMemoryFileFetcher( $content ), new NullLogger() );
	}

}
