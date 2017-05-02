<?php
declare( strict_types = 1 );

namespace Unit\Infrastructure;

use FileFetcher\FileFetchingException;
use FileFetcher\InMemoryFileFetcher;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Infrastructure\WordListFileReader;

class WordListFileReaderTest extends TestCase {

	public function testGivenEmptyString_anEmptyListIsReturned() {
		$stringList = new WordListFileReader( new InMemoryFileFetcher( ['empty.txt' => ''] ), 'empty.txt' );
		$this->assertSame( [], $stringList->toArray() );
	}

	public function testGivenEmptyFilename_anEmptyListIsReturned() {
		$stringList = new WordListFileReader( new InMemoryFileFetcher( ['empty.txt' => ''] ), '' );
		$this->assertSame( [], $stringList->toArray() );
	}

	public function testGivenMissingFile_fileFetchingExceptionBubblesUp() {
		// In production, the FileFetcher should be wrapped in an ErrorLoggingFileFetcher
		$this->expectException( FileFetchingException::class );
		$stringList = new WordListFileReader( new InMemoryFileFetcher( [] ), 'utopia.txt' );
		$stringList->toArray();
	}

	public function testGivenMultilineFile_eachLineIsReturned() {
		$stringList = new WordListFileReader(
			new InMemoryFileFetcher( ['words.txt' => "one\ntwo\nthree\ntechno"] ),
			'words.txt'
		);
		$this->assertSame( ['one', 'two', 'three', 'techno'], $stringList->toArray() );
	}

	public function testGivenFileWithEmptyLinesAndTabs_whitespaceIsStripped() {
		$stringList = new WordListFileReader(
			new InMemoryFileFetcher( ['words.txt' => "one  \n\ttwo\n\t\tthree\n\n\t\t\ttechno\r\n"] ),
			'words.txt'
		);
		$this->assertEquals( ['one', 'two', 'three', 'techno'], $stringList->toArray() );
	}

}
