<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Presentation\FilePrefixer;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DisplayPageRouteTest extends WebRouteTestCase {

	private $notFoundMessage;

	// @codingStandardsIgnoreStart
	protected function onTestEnvironmentCreated( FunFunFactory $factory, array $config ) {
		// @codingStandardsIgnoreEnd
		$this->notFoundMessage = $factory->getTranslator()->trans( 'page_not_found' );
	}

	public function testWhenPageDoesNotExist_missingResponseIsReturned() {
		$client = $this->createClient();
		$client->request( 'GET', '/page/kittens' );

		$this->assertContains(
			$this->notFoundMessage,
			$client->getResponse()->getContent()
		);
	}

	public function testFooterAndHeaderGetEmbedded() {
		$client = $this->createClient();
		$client->request( 'GET', '/page/kittens' );

		$this->assertContains(
			'page header',
			$client->getResponse()->getContent()
		);

		$this->assertContains(
			'page footer',
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
		$client = $this->createClient( [
			'twig' => [
				'loaders' => [
					'array' => [
						'kittens.html.twig' => '{$ basepath $}/someFile.css'
					]
				]
			]
		] );
		$client->request( 'GET', '/page/kittens' );

		$this->assertContains(
			'/someFile.css',
			$client->getResponse()->getContent()
		);
	}

	// @todo
	public function testWhenWebBasePathIsSet_itIsUsedInTemplatedPaths() {
		$client = $this->createClient( [
			'twig' => [
				'loaders' => [
					'array' => [
						'kittens.html.twig' => '{$ basepath $}/someFile.css'
					]
				]
			],
			'web-basepath' => '/some-path/someFile.css'
		] );
		$client->request( 'GET', '/page/kittens' );

		$this->assertContains(
			'/some-path/someFile.css',
			$client->getResponse()->getContent()
		);
	}

	public function testWhenRequestedPageExists_itGetsEmbedded() {
		$client = $this->createClient( [
			'twig' => [
				'loaders' => [
					'array' => [
						'unicorns.html.twig' => '<p>Pink fluffy unicorns dancing on rainbows</p>'
					]
				]
			]
		] );

		$client->request( 'GET', '/page/unicorns' );

		$this->assertContains(
			'<p>Pink fluffy unicorns dancing on rainbows</p>',
			$client->getResponse()->getContent()
		);

		// Test header, footer and noJS feature of the base template
		$this->assertContains(
			'page header',
			$client->getResponse()->getContent()
		);

		$this->assertContains(
			'page footer',
			$client->getResponse()->getContent()
		);

		$this->assertContains(
			'Y u no JavaScript!',
			$client->getResponse()->getContent()
		);
	}

	public function testWhenPageNameContainsSlash_404isReturned() {
		$client = $this->createClient( [], null, self::DISABLE_DEBUG );
		$client->request( 'GET', '/page/unicorns/of-doom' );

		$this->assert404( $client->getResponse() );
	}

	public function testWhenRequestedPageIsTranslated_itIsDisplayed() {
		$client = $this->createClient();
		$client->request( 'GET', '/page/Translated_Page' );
		$this->assertContains( 'This is just a test.', $client->getResponse()->getContent() );
	}

	public function testFilePrefixerIsCalledInTemplate() {
		$client = $this->createClient(
			[
				'twig' => [
					'loaders' => [
						'array' => [
							'unicorns.html.twig' => '{$ "testfile.js"|prefix_file $}'
						]
					]
				]
			],
			function ( FunFunFactory $factory ) {
				$prefixer = new FilePrefixer( 'mylittleprefix' );
				$factory->setFilePrefixer( $prefixer );
			}
		);

		$client->request( 'GET', '/page/unicorns' );

		$this->assertContains(
			'mylittleprefix.testfile.js',
			$client->getResponse()->getContent()
		);
	}
}
