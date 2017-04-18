<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Silex\Application;
use Symfony\Component\HttpKernel\Client;
use Twig_Loader_Array;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Presentation\ContentPage\PageNotFoundException;
use WMDE\Fundraising\Frontend\Presentation\ContentPage\PageSelector;
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

	public function testWhenPageDoesNotExist_missingResponseIsReturnedAndHasHeaderAndFooter() {
		$client = $this->createClient(
			[],
			function ( FunFunFactory $factory ) {
				$pageSelector = $this->createMock( PageSelector::class );
				$pageSelector
					->method( 'getPageId' )
					->with('kittens')
					->willThrowException( new PageNotFoundException() );
				$factory->setContentPagePageSelector( $pageSelector );
			}
		);
		$client->request( 'GET', '/page/kittens' );

		$content = $client->getResponse()->getContent();

		$this->assertContains(
			$this->notFoundMessage,
			$content
		);

		$this->assertContains(
			'page header',
			$content
		);

		$this->assertContains(
			'page footer',
			$content
		);
	}

	public function testWhenPageDoesNotExist_noUnescapedPageNameIsShown() {
		$client = $this->createClient(
			[],
			function ( FunFunFactory $factory ) {
				$pageSelector = $this->createMock( PageSelector::class );
				$pageSelector
					->method( 'getPageId' )
					->withAnyParameters()
					->willThrowException( new PageNotFoundException() );
				$factory->setContentPagePageSelector( $pageSelector );
			}
		);
		$client->request( 'GET', '/page/<script>alert("kittens");' );

		$this->assertNotContains(
			'<script>alert("kittens")',
			$client->getResponse()->getContent()
		);
	}

	public function testWhenRequestedContentPageExists_itGetsEmbeddedAndHasHeaderAndFooter() {
		$this->createAppEnvironment(
			[ ],
			function ( Client $client, FunFunFactory $factory, Application $app ) {

				// @todo Make this the default behaviour of WebRouteTestCase::createAppEnvironment()
				$factory->setTwigEnvironment( $app['twig'] );

				$pageSelector = $this->createMock( PageSelector::class );
				$pageSelector
					->method( 'getPageId' )
					->willReturnArgument(0)
					->with( 'einhorns' )
					->willReturn( 'unicorns' );
				$factory->setContentPagePageSelector( $pageSelector );

				$factory->setContentPageTemplateLoader(
					new Twig_Loader_Array( [
						'unicorns.html.twig' => '<p>Rosa plüsch einhorns tanzen auf Regenbogen</p>',
					] )
				);

				$client->request( 'GET', '/page/einhorns' );

				$content = $client->getResponse()->getContent();

				$this->assertContains(
					'<p>Rosa plüsch einhorns tanzen auf Regenbogen</p>',
					$content
				);

				// Test header, footer and noJS feature of the base template
				$this->assertContains(
					'page header',
					$content
				);

				$this->assertContains(
					'page footer',
					$content
				);

				$this->assertContains(
					'Y u no JavaScript!',
					$content
				);
			}
		);
	}

	public function testWhenPageNameContainsSlash_404isReturned() {
		$client = $this->createClient( [], null, self::DISABLE_DEBUG );
		$client->request( 'GET', '/page/unicorns/of-doom' );

		$this->assert404( $client->getResponse() );
	}
}
