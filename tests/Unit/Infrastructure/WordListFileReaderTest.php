<?php

declare( strict_types = 1 );

namespace Unit\Infrastructure;

use FileFetcher\InMemoryFileFetcher;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use WMDE\Fundraising\Frontend\Infrastructure\ErrorLoggingFileFetcher;
use WMDE\Fundraising\Frontend\Infrastructure\WordListFileReader;

class WordListFileReaderTest extends TestCase {

	public function testGivenEmptyString_anEmptyListIsReturned() {
		$stringList = new WordListFileReader( $this->getFileFetcherWithContent( [ 'empty.txt' => '' ] ), 'empty.txt' );
		$this->assertSame( [], $stringList->toArray() );
	}

	public function testGivenEmptyFilename_anEmptyListIsReturned() {
		$stringList = new WordListFileReader( $this->getFileFetcherWithContent( [ 'empty.txt' => '' ] ), '' );
		$this->assertSame( [], $stringList->toArray() );
	}

	public function testGivenMissingFile_fileFetchingExceptionIsCaught() {
		$stringList = new WordListFileReader( $this->getFileFetcherWithContent( [] ), 'utopia.txt' );
		$this->assertSame( [], $stringList->toArray() );
	}

	public function testGivenMultilineFile_eachLineIsReturned() {
		$stringList = new WordListFileReader(
			$this->getFileFetcherWithContent( [ 'words.txt' => "one\ntwo\nthree\ntechno" ] ),
			'words.txt'
		);
		$this->assertSame( [ 'one', 'two', 'three', 'techno' ], $stringList->toArray() );
	}

	public function testGivenFileWithEmptyLinesAndTabs_whitespaceIsStripped() {
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
