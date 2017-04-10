<?php

declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation\ContentPage;

use WMDE\Fundraising\Frontend\Presentation\ContentPage\PageSelector;
use WMDE\Fundraising\Frontend\Presentation\ContentPage\PageNotFoundException;
use PHPUnit\Framework\TestCase;

class PageSelectorTest extends TestCase {
	/**
	 * @var PageSelector
	 */
	private $sut;

	public function setUp(): void {
		$this->sut = new PageSelector( [
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
		$this->assertSame( 'something', $this->sut->getPageId( 'page1' ) );
		$this->assertSame( 'else', $this->sut->getPageId( 'page2' ) );
		$this->assertSame( 'harry', $this->sut->getPageId( 'something' ) );
		$this->assertSame( 'german', $this->sut->getPageId( 'überraschung' ) );
	}

	/**
	 * @dataProvider slugsProducingException
	 */
	public function testASlugDoesNotExist_anExceptionIsThrown( string $slug ): void {
		$this->expectException( PageNotFoundException::class );
		$this->sut->getPageId( $slug );
	}

	public function slugsProducingException(): array {
		return [
				[ 'PAGE1' ],
				[ 'page3' ],
		];
	}
}
