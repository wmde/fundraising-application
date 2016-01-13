<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit\PageRetriever;

use FileFetcher\FileFetcher;
use FileFetcher\FileFetchingException;
use Psr\Log\LoggerInterface;
use WMDE\Fundraising\Frontend\PageRetriever\ActionBasedPageRetriever;

/**
 * @covers WMDE\Fundraising\Frontend\PageRetriever\ActionBasedPageRetriever
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Kai Nissen
 * @author Christoph Fischer
 */
class ActionBasedPageRetrieverTest extends \PHPUnit_Framework_TestCase {

	private function newLoggerMock() {
		return $this->getMockBuilder( LoggerInterface::class )
			->disableOriginalConstructor()
			->getMock();
	}

	private function newPageRetriever( $fetcherReturnValue ) {
		$fetcher = $this->getMock( FileFetcher::class );

		$fetcher->expects( $this->once() )
			->method( 'fetchFile' )
			->with( $this->equalTo( 'http://example.com?title=foo&action=render' ) )
			->will( $this->returnValue( $fetcherReturnValue ) );

		return new ActionBasedPageRetriever(
			'http://example.com',
			$this->newLoggerMock(),
			$fetcher
		);
	}

	private function assertFetcherResultValueIsReturnedAs( $expectedReturnValue, $fetcherReturnValue ) {
		$pageFetcher = $this->newPageRetriever( $fetcherReturnValue );

		$this->assertSame( $expectedReturnValue, $pageFetcher->fetchPage( 'foo', 'render' ) );
	}

	public function testWhenFetcherReturnsSomeValue_fetchPageReturnsTheValue() {
		$this->assertFetcherResultValueIsReturnedAs( '', '' );
		$this->assertFetcherResultValueIsReturnedAs( 'foobar', 'foobar' );
	}

	public function testWhenWikiDoctypeHtmlString_fetchPageReturnsEmptyString() {
		$this->assertFetcherResultValueIsReturnedAs( '', '<!DOCTYPE html>' );
	}

	public function testWhenFetcherThrowsException_fetchPageReturnsEmptyString() {
		$fetcher = $this->getMock( FileFetcher::class );

		$fetcher->expects( $this->once() )
			->method( 'fetchFile' )
			->willThrowException( new FileFetchingException( 'spam' ) );

		$pageFetcher = new ActionBasedPageRetriever(
			'http://example.com',
			$this->newLoggerMock(),
			$fetcher
		);

		$this->assertSame( '', $pageFetcher->fetchPage( 'foo', 'bar' ) );
	}

}
