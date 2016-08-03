<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use WMDE\Fundraising\Frontend\ApplicationContext\Infrastructure\CachingPageRetriever;
use WMDE\Fundraising\Frontend\ApplicationContext\Infrastructure\PageRetriever;

/**
 * @covers WMDE\Fundraising\Frontend\ApplicationContext\Infrastructure\CachingPageRetriever
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CachingPageRetrieverTest extends \PHPUnit_Framework_TestCase {

	const LIVE_CONTENT = 'some non-cached content';
	const CACHED_CONTENT = 'cached page content';

	const PAGE_NAME = 'Oracle Kai';

	public function testWhenPageNotInCache_itGetsFetched() {
		$cachingRetriever = new CachingPageRetriever(
			$this->newLivePageRetriever(),
			$this->newCacheWithNoMatchingPages()
		);

		$this->assertSame(
			self::LIVE_CONTENT,
			$cachingRetriever->fetchPage( self::PAGE_NAME, PageRetriever::MODE_RAW )
		);
	}

	/**
	 * @return PageRetriever|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function newLivePageRetriever(): PageRetriever {
		$pageRetriever = $this->createMock( PageRetriever::class );
		$pageRetriever->method( 'fetchPage' )->willReturn( self::LIVE_CONTENT );
		return $pageRetriever;
	}

	/**
	 * @return Cache|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function newCacheWithNoMatchingPages(): Cache {
		$cache = new ArrayCache();

		$cache->save( 'key that will not match', 'content that should not be returned' );
		$cache->save( 'another key that will not match', 'content that should not be returned' );

		return $cache;
	}

	public function testWhenPageNotInCache_itGetsPutIntoTheCache() {
		$cache = $this->newCacheWithNoMatchingPages();

		$cachingRetriever = new \WMDE\Fundraising\Frontend\ApplicationContext\Infrastructure\CachingPageRetriever(
			$this->newLivePageRetriever(),
			$cache
		);

		$cachingRetriever->fetchPage( self::PAGE_NAME, PageRetriever::MODE_RAW );

		$this->assertSame(
			self::LIVE_CONTENT,
			$cache->fetch( self::PAGE_NAME )
		);
	}

	public function testWhenPageInCache_onlyTheCacheIsUsed() {
		$pageRetriever = $this->newLivePageRetriever();
		$pageRetriever->expects( $this->never() )->method( $this->anything() );

		$cachingRetriever = new CachingPageRetriever(
			$pageRetriever,
			$this->newCacheWithMatchingPage()
		);

		$this->assertSame(
			self::CACHED_CONTENT,
			$cachingRetriever->fetchPage( self::PAGE_NAME, PageRetriever::MODE_RAW )
		);
	}

	private function newCacheWithMatchingPage(): Cache {
		$cache = $this->newCacheWithNoMatchingPages();

		$cache->save( self::PAGE_NAME, self::CACHED_CONTENT );

		return $cache;
	}

}
