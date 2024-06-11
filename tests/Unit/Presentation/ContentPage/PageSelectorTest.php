<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation\ContentPage;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Presentation\ContentPage\PageNotFoundException;
use WMDE\Fundraising\Frontend\Presentation\ContentPage\PageSelector;

/**
 * @covers \WMDE\Fundraising\Frontend\Presentation\ContentPage\PageSelector
 */
class PageSelectorTest extends TestCase {
	private PageSelector $pageSelector;

	public function setUp(): void {
		$this->pageSelector = new PageSelector( [
				'something' => 'page1',
				'else' => 'page2',
				'harry' => 'something',
				'german' => 'überraschung',
		] );
	}

	public function testMalconfiguredConfig_theFirstMatchingPageIsReturned(): void {
		$sut = new PageSelector( [
				'a' => 'something',
				'b' => 'something',
		] );

		$this->assertSame( 'a', $sut->getPageId( 'something' ) );
	}

	public function testASlugExists_thePageNameIsReturned(): void {
		$this->assertSame( 'something', $this->pageSelector->getPageId( 'page1' ) );
		$this->assertSame( 'else', $this->pageSelector->getPageId( 'page2' ) );
		$this->assertSame( 'harry', $this->pageSelector->getPageId( 'something' ) );
		$this->assertSame( 'german', $this->pageSelector->getPageId( 'überraschung' ) );
	}

	/**
	 * @dataProvider slugsProducingException
	 */
	public function testASlugDoesNotExist_anExceptionIsThrown( string $slug ): void {
		$this->expectException( PageNotFoundException::class );
		$this->pageSelector->getPageId( $slug );
	}

	/**
	 * @return string[][]
	 */
	public static function slugsProducingException(): array {
		return [
				[ 'PAGE1' ],
				[ 'page3' ],
		];
	}
}
