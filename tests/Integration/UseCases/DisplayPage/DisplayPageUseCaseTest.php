<?php

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\DisplayPage;

use Mediawiki\Api\MediawikiApi;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ApiPostRequestHandler;
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
	 * @var TestEnvironment
	 */
	private $testEnvironment;

	public function setUp() {
		$this->testEnvironment = TestEnvironment::newInstance();
		parent::setUp();
	}

	private function registerWikiPages() {
		$api = $this->getMockBuilder( MediawikiApi::class )->disableOriginalConstructor()->getMock();

		$api->expects( $this->any() )
			->method( 'postRequest' )
			->willReturnCallback( new ApiPostRequestHandler( $this->testEnvironment ) );

		$this->testEnvironment->getFactory()->setMediaWikiApi( $api );
	}

	public function testWhenPageExists_itGetsEmbedded() {
		$this->registerWikiPages();

		$useCase = $this->testEnvironment->getFactory()->newDisplayPageUseCase();

		$response = $useCase->getPage( new PageDisplayRequest( 'Unicorns' ) );

		$this->assertSame( '<p>Pink fluffy unicorns dancing on rainbows</p>', $response->getMainContent() );
		$this->assertSame( '<p>I\'m a header</p>', $response->getHeaderContent() );
		$this->assertSame( '<p>I\'m a footer</p>', $response->getFooterContent() );
	}

	public function testWhenPageTitlePrefixIsConfigured_pageCanBeRetrieved() {
		$this->registerWikiPages();

		$factory = $this->testEnvironment->getFactory();
		$factory->setPageTitlePrefix( 'MyNamespace:MyPrefix/' );
		$useCase = $factory->newDisplayPageUseCase();

		$response = $useCase->getPage( new PageDisplayRequest( 'Naked mole-rat' ) );

		$this->assertSame( '<p>This little guy wants to cuddle, too!</p>', $response->getMainContent() );
	}

}
