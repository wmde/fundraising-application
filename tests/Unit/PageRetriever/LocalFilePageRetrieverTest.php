<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit\PageRetriever;

use FileFetcher\FileFetcher;
use Psr\Log\LoggerInterface;
use WMDE\Fundraising\Frontend\PageRetriever\LocalFilePageRetriever;

/**
 * @covers WMDE\Fundraising\Frontend\PageRetriever\LocalFilePageRetriever
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen
 */
class LocalFilePageRetrieverTest extends \PHPUnit_Framework_TestCase {

	private function createPageRetriever( $fileName, $expectedReturnValue ) {
		$debugger = $this->getMock( LoggerInterface::class );
		$fetcher = $this->getMock( FileFetcher::class );

		$fetcher->expects( $this->once() )
			->method( 'fetchFile' )
			->with( $fileName )
			->will( $this->returnValue( $expectedReturnValue ) );

		return new LocalFilePageRetriever( $fetcher, $debugger );
	}

	public function testLocalFilePageRetrieverReturnsContent() {
		$pageRetriever = $this->createPageRetriever( 'foo', 'some string' );
		$this->assertSame( 'some string', $pageRetriever->fetchPage( 'foo' ) );
	}

}
