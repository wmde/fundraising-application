<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation\Content;

use WMDE\Fundraising\Frontend\Presentation\Content\TwigPageLoader;
use WMDE\PageRetriever\PageRetriever;

class TwigPageLoaderTest extends \PHPUnit\Framework\TestCase {

	public function testGivenAPageText_getSourceReturnsPageText() {
		$loader = new TwigPageLoader(
			$this->newPageRetrieverThatWillReturn( 'template text' )
		);

		$this->assertSame( 'template text', $loader->getSource( 'Felis silvestris' ) );
	}

	/**
	 * @param string $content
	 * @return PageRetriever|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function newPageRetrieverThatWillReturn( string $content ): PageRetriever {
		$pageRetriever = $this->createMock( PageRetriever::class );
		$pageRetriever->method( 'fetchPage' )->willReturn( $content );
		return $pageRetriever;
	}

	public function testGivenAPageText_getCacheKeyReturnsPageName() {
		$loader = new TwigPageLoader(
			$this->newPageRetrieverThatWillReturn( 'template text' )
		);

		$this->assertSame( 'Felis silvestris', $loader->getCacheKey( 'Felis silvestris' ) );
	}

	public function testGivenAPageText_isFreshKeyReturnsTrue() {
		$loader = new TwigPageLoader(
			$this->newPageRetrieverThatWillReturn( 'template text' )
		);

		$this->assertTrue( $loader->isFresh( 'Felis silvestris', 0 ) );
	}

	/**
	 * @expectedException \Twig_Error_Loader
	 */
	public function testGivenMissingPage_getSourceThrowsException() {
		$loader = new TwigPageLoader(
			$this->newPageRetrieverThatWillReturn( '' )
		);

		$loader->getSource( 'Felis silvestris' );
	}

	/**
	 * @expectedException \Twig_Error_Loader
	 */
	public function testGivenMissingPage_getCacheKeyThrowsException() {
		$loader = new TwigPageLoader(
			$this->newPageRetrieverThatWillReturn( '' )
		);

		$loader->getCacheKey( 'Felis silvestris' );
	}

	public function testGivenMissingPage_isFreshKeyStaysTrue() {
		$loader = new TwigPageLoader(
			$this->newPageRetrieverThatWillReturn( 'template text' )
		);

		$this->assertTrue( $loader->isFresh( 'Felis silvestris', 0 ) );
	}

}
