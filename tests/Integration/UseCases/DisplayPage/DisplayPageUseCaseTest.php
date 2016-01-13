<?php

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\DisplayPage;

use FileFetcher\InMemoryFileFetcher;
use WMDE\Fundraising\Frontend\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;
use WMDE\Fundraising\Frontend\UseCases\DisplayPage\PageDisplayRequest;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\DisplayPage\DisplayPageUseCase
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DisplayPageUseCaseTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var FunFunFactory
	 */
	private $factory;

	public function setUp() {
		$this->factory = TestEnvironment::newInstance()->getFactory();
		parent::setUp();
	}

	private function registerWikiPages() {
		$this->factory->setFileFetcher( new InMemoryFileFetcher( [
			'http://cms.wiki/?title=Unicorns&action=render' => 'Pink fluffy unicorns dancing on rainbows',
			'http://cms.wiki/?title=10hoch16%2FSeitenkopf&action=render' => '<div>An awesome header</div>',
			'http://cms.wiki/?title=10hoch16%2FSeitenfu%C3%9F&action=render' => '<div>An awesome footer</div>',
		] ) );
	}

	public function testWhenPageExists_itGetsEmbedded() {
		$this->registerWikiPages();
		$useCase = $this->factory->newDisplayPageUseCase();

		$response = $useCase->getPage( new PageDisplayRequest( 'Unicorns' ) );

		$this->assertSame( 'Pink fluffy unicorns dancing on rainbows', $response->getMainContent() );
		$this->assertSame( '<div>An awesome header</div>', $response->getHeaderContent() );
		$this->assertSame( '<div>An awesome footer</div>', $response->getFooterContent() );
	}

}
