<?php


namespace WMDE\Fundraising\Frontend\Tests\Unit;

use WMDE\Fundraising\Frontend\Presenters\Content\WikiContentProvider;
use WMDE\Fundraising\Frontend\TwigPageLoader;

class TwigPageLoaderTest extends \PHPUnit_Framework_TestCase {

	public function testGivenAPageText_getSourceReturnsPageText() {
		$pageRetriever = $this->getMockBuilder( WikiContentProvider::class )->disableOriginalConstructor()->getMock();
		$pageRetriever->method( 'getContent' )->willReturn( 'template text' );
		$loader = new TwigPageLoader( $pageRetriever );
		$this->assertSame( 'template text', $loader->getSource( 'Felis silvestris' ) );
	}

	public function testGivenAPageText_getCacheKeyReturnsPageName() {
		$pageRetriever = $this->getMockBuilder( WikiContentProvider::class )->disableOriginalConstructor()->getMock();
		$pageRetriever->method( 'getContent' )->willReturn( 'template text' );
		$loader = new TwigPageLoader( $pageRetriever );
		$this->assertSame( 'Felis silvestris', $loader->getCacheKey( 'Felis silvestris' ) );
	}

	public function testGivenAPageText_isFreshKeyReturnsTrue() {
		$pageRetriever = $this->getMockBuilder( WikiContentProvider::class )->disableOriginalConstructor()->getMock();
		$pageRetriever->method( 'getContent' )->willReturn( 'template text' );
		$loader = new TwigPageLoader( $pageRetriever );
		$this->assertTrue( $loader->isFresh( 'Felis silvestris', 0 ) );
	}

	/**
	 * @expectedException \Twig_Error_Loader
	 */
	public function testGivenMissingPage_getSourceThrowsException() {
		$pageRetriever = $this->getMockBuilder( WikiContentProvider::class )->disableOriginalConstructor()->getMock();
		$pageRetriever->method( 'getContent' )->willReturn( '' );
		$loader = new TwigPageLoader( $pageRetriever );
		$loader->getSource( 'Felis silvestris' );
	}

	/**
	 * @expectedException \Twig_Error_Loader
	 */
	public function testGivenMissingPage_getCacheKeyThrowsException() {
		$pageRetriever = $this->getMockBuilder( WikiContentProvider::class )->disableOriginalConstructor()->getMock();
		$pageRetriever->method( 'getContent' )->willReturn( '' );
		$loader = new TwigPageLoader( $pageRetriever );
		$loader->getCacheKey( 'Felis silvestris' );
	}

	/**
	 * @expectedException \Twig_Error_Loader
	 */
	public function testGivenMissingPage_isFreshKeyThrowsException() {
		$pageRetriever = $this->getMockBuilder( WikiContentProvider::class )->disableOriginalConstructor()->getMock();
		$pageRetriever->method( 'getContent' )->willReturn( '' );
		$loader = new TwigPageLoader( $pageRetriever );
		$loader->isFresh( 'Felis silvestris', 0 );
	}
}
