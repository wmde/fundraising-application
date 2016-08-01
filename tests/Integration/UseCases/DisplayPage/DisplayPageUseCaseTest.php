<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\DisplayPage;

use Mediawiki\Api\MediawikiApi;
use WMDE\Fundraising\Frontend\ApplicationContext\UseCases\DisplayPage\PageDisplayRequest;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ApiPostRequestHandler;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @covers WMDE\Fundraising\Frontend\ApplicationContext\UseCases\DisplayPage\DisplayPageUseCase
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
		$twigConfig = [
			'twig' => [
				'loaders' => [
					'wiki' => [
						'enabled' => true
					]
				]
			]
		];
		$this->testEnvironment = TestEnvironment::newInstance( $twigConfig );
		parent::setUp();
	}

	private function registerWikiPages() {
		$api = $this->getMockBuilder( MediawikiApi::class )->disableOriginalConstructor()->getMock();

		$api->expects( $this->any() )
			->method( 'postRequest' )
			->willReturnCallback( new ApiPostRequestHandler( $this->testEnvironment ) );

		$this->testEnvironment->getFactory()->setMediaWikiApi( $api );
	}

	public function testWhenPageExists_responseReflectsExistence() {
		$this->registerWikiPages();

		$useCase = $this->testEnvironment->getFactory()->newDisplayPageUseCase();

		$response = $useCase->getPage( new PageDisplayRequest( 'Unicorns' ) );

		$this->assertTrue( $response->getTemplateExists() );
		$this->assertSame( 'Unicorns', $response->getMainContentTemplate() );
	}

	public function testWhenPageTitlePrefixIsConfigured_pageCanBeRetrieved() {
		$this->registerWikiPages();

		$factory = $this->testEnvironment->getFactory();
		$factory->setPageTitlePrefix( 'MyNamespace:MyPrefix/' );
		$useCase = $factory->newDisplayPageUseCase();

		$response = $useCase->getPage( new PageDisplayRequest( 'Naked mole-rat' ) );

		$this->assertTrue( $response->getTemplateExists() );
		$this->assertSame( 'Naked mole-rat', $response->getMainContentTemplate() );
	}

}
