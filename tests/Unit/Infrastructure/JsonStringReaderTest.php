<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use FileFetcher\InMemoryFileFetcher;
use FileFetcher\ThrowingFileFetcher;
use WMDE\Fundraising\Frontend\Infrastructure\JsonStringReader;

class JsonStringReaderTest extends \PHPUnit\Framework\TestCase {

	public function testGivenFailingFileFetcher_exceptionIsThrown() {
		$this->expectException( \RuntimeException::class );
		( new JsonStringReader(
			'/test/directory/wrong.file',
			new ThrowingFileFetcher()
		) )->readAndValidateJson();
	}

	public function testGivenEmptyJsonFile_exceptionIsThrown() {
		$this->expectException( \RuntimeException::class );
		( new JsonStringReader(
			'/test/directory/empty.json',
			$this->getFileFetcherWithContent( [ '/test/directory/empty.json' => '' ] )
		) )->readAndValidateJson();
	}

	private function getFileFetcherWithContent( array $content ): InMemoryFileFetcher {
		return new InMemoryFileFetcher( $content );
	}

	public function testGivenInvalidJsonFile_exceptionIsThrown() {
		$this->expectException( \RuntimeException::class );
		( new JsonStringReader(
			'/test/directory/invalid.json',
			$this->getFileFetcherWithContent( [ '/test/directory/invalid.json' => '{ test": "is" "broken": "now"' ] )
		) )->readAndValidateJson();
	}

	public function testGivenValidJson_contentIsReturned() {
		$validJson = ( new JsonStringReader(
			'/test/directory/valid.json',
			$this->getFileFetcherWithContent( [ '/test/directory/valid.json' => '{"test": "is", "ok": "now"}' ] )
		) )->readAndValidateJson();
		$this->assertEquals( $validJson, '{"test": "is", "ok": "now"}' );
	}
}
