<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Mediawiki\Api\ApiUser;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\Request;
use Mediawiki\Api\UsageException;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ApiPostRequestHandler;

/**
 * @covers WMDE\Fundraising\Frontend\Presentation\Presenters\DisplayPagePresenter
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DisplayPageRouteTest extends WebRouteTestCase {

	// @codingStandardsIgnoreStart
	protected function onTestEnvironmentCreated( FunFunFactory $factory, array $config ) {
		// @codingStandardsIgnoreEnd
		$api = $this->getMockBuilder( MediawikiApi::class )->disableOriginalConstructor()->getMock();

		$api->expects( $this->any() )
			->method( 'postRequest' )
			->willReturnCallback( function( Request $request ) {
				throw new UsageException( 'Page not found: ' . $request->getParams()['page'] );
			} );

		$factory->setMediaWikiApi( $api );
	}

	public function testWhenPageDoesNotExist_missingResponseIsReturned() {
		$client = $this->createClient();
		$client->request( 'GET', '/page/kittens' );

		$this->assertContains(
			'Could not load main content!',
			$client->getResponse()->getContent()
		);
	}

	public function testFooterAndHeaderGetEmbedded() {
		$client = $this->createClient();
		$client->request( 'GET', '/page/kittens' );

		$this->assertContains(
			'Could not load header!',
			$client->getResponse()->getContent()
		);

		$this->assertContains(
			'Could not load footer!',
			$client->getResponse()->getContent()
		);
	}

	public function testWhenPageDoesNotExist_noUnescapedPageNameIsShown() {
		$client = $this->createClient();
		$client->request( 'GET', '/page/<script>alert("kittens");' );

		$this->assertNotContains(
			'<script>alert("kittens")',
			$client->getResponse()->getContent()
		);
	}

	public function testWhenWebBasePathIsEmpty_templatedPathsReferToRootPath() {
		$client = $this->createClient();
		$client->request( 'GET', '/page/kittens' );

		$this->assertContains(
			'"/res/css/fontcustom.css"',
			$client->getResponse()->getContent()
		);
	}

	public function testWhenWebBasePathIsSet_itIsUsedInTemplatedPaths() {
		$client = $this->createClient( [ 'web-basepath' => '/some-path' ] );
		$client->request( 'GET', '/page/kittens' );

		$this->assertContains(
			'"/some-path/res/css/fontcustom.css"',
			$client->getResponse()->getContent()
		);
	}

	public function testWhenRequestedPageExists_itGetsEmbedded() {
		$client = $this->createClient(
			[
				'twig' => [
					'loaders' => [
						'array' => [
							'10hoch16/Seitenkopf' => '<p>I\'m a header</p>',
							'10hoch16/Seitenfuß' => '<p>I\'m a footer</p>',
							'JavaScript-Notice' => '<p>Y u no JavaScript!</p>',
							],
						'wiki' => [
							'enabled' => true
							]
						]
					]
			],
		function( FunFunFactory $factory, array $config ) {
			$api = $this->getMockBuilder( MediawikiApi::class )->disableOriginalConstructor()->getMock();

			$api->expects( $this->atLeastOnce() )
				->method( 'login' )
				->with( new ApiUser(
					$config['cms-wiki-user'],
					$config['cms-wiki-password']
				) );

			$api->expects( $this->any() )
				->method( 'postRequest' )
				->willReturnCallback( new ApiPostRequestHandler() );

			$factory->setMediaWikiApi( $api );
		} );

		$client->request( 'GET', '/page/unicorns' );

		$this->assertContains(
			'<p>Pink fluffy unicorns dancing on rainbows</p>',
			$client->getResponse()->getContent()
		);

		// Test header, footer and noJS feature of the base template
		$this->assertContains(
			'<p>I\'m a header</p>',
			$client->getResponse()->getContent()
		);

		$this->assertContains(
			'<p>I\'m a footer</p>',
			$client->getResponse()->getContent()
		);

		$this->assertContains(
			'<p>Y u no JavaScript!</p>',
			$client->getResponse()->getContent()
		);
	}

	public function testWhenPageNameContainsSlash_404isReturned() {
		$client = $this->createClient( [], null, self::DISABLE_DEBUG );
		$client->request( 'GET', '/page/unicorns/of-doom' );

		$this->assert404( $client->getResponse() );
	}

}
