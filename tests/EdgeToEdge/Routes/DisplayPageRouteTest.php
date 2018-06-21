<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use org\bovigo\vfs\vfsStream;
use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Presentation\ContentPage\PageNotFoundException;
use WMDE\Fundraising\Frontend\Presentation\ContentPage\PageSelector;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\ContentProvider\ContentProvider;
use WMDE\Fundraising\Frontend\Tests\Fixtures\OverridingCampaignConfigurationLoader;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DisplayPageRouteTest extends WebRouteTestCase {

	public function testWhenPageHasCustomTemplate_customTemplateIsRendered(): void {
		$client = $this->createClient(
			[],
			function ( FunFunFactory $factory ): void {
				$this->setDefaultSkin( $factory, 'cat17' );
			}
		);
		//Requesting page_layouts/supporters.html.twig from cat17 skin
		$client->request(
			'GET',
			'/page/Unterstützerliste'
		);
		$content = $client->getResponse()->getContent();

		//Checking if supporters.js is added by the custom page layout
		$this->assertContains( '/skins/cat17/scripts/supporters.js', $content );
	}

	public function testWhenPageDoesNotExist_missingResponseIsReturnedAndHasHeaderAndFooter(): void {
		$client = $this->createClient(
			[],
			function ( FunFunFactory $factory ): void {
				$this->setDefaultSkin( $factory, 'cat17' );
			}
		);
		$client->request( 'GET', '/page/kittens' );
		$crawler = $client->getCrawler();

		$this->assertSame( 1, $crawler->filter( '.page-not-found' )->count() );
		$this->assertSame( 1, $crawler->filter( 'header' )->count() );
		$this->assertSame( 1, $crawler->filter( 'footer' )->count() );
	}

	public function testWhenPageDoesNotExist_noUnescapedPageNameIsShown(): void {
		$client = $this->createClient(
			[],
			function ( FunFunFactory $factory ): void {
				$this->setDefaultSkin( $factory, 'cat17' );
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

	public function testWhenRequestedContentPageExists_itGetsEmbeddedAndHasHeaderAndFooter(): void {
		$this->createEnvironment(
			[],
			function ( Client $client, FunFunFactory $factory ): void {
				$this->setDefaultSkin( $factory, 'cat17' );

				$factory->setContentPagePageSelector( $this->newMockPageSelector( 'unicorns' ) );
				$factory->setContentProvider(
					$this->newVfsContentProvider(
						[ 'unicorns.twig' => '<p>Rosa plüsch einhorns tanzen auf Regenbogen</p>' ]
					)
				);

				$crawler = $client->request( 'GET', '/page/unicorns' );

				$this->assertCount( 1, $crawler->filter( 'body .page-unicorns' ) );
				$this->assertCount( 1, $crawler->filter( 'header > .container' ) );
				$this->assertCount(
					1,
					$crawler->filter( 'main .content p:contains("Rosa plüsch einhorns tanzen auf Regenbogen")' )
				);
				$this->assertCount( 1, $crawler->filter( 'footer > .container' ) );
			}
		);
	}

	public function testWhenPageNameContainsSlash_404isReturned(): void {
		$client = $this->createClient(
			[],
			function ( FunFunFactory $factory ): void {
				$this->setDefaultSkin( $factory, 'cat17' );
			},
			self::DISABLE_DEBUG
		);
		$client->request( 'GET', '/page/unicorns/of-doom' );

		$this->assert404( $client->getResponse() );
	}

	private function newMockPageSelector( string $pageId ): PageSelector {
		$pageSelector = $this->createMock( PageSelector::class );
		$pageSelector
			->method( 'getPageId' )
			->willReturnArgument( 0 )
			->with( $pageId )
			->willReturn( $pageId );
		return $pageSelector;
	}

	private function newNotFoundPageSelector( string $pageId ): PageSelector {
		$pageSelector = $this->createMock( PageSelector::class );
		$pageSelector
			->method( 'getPageId' )
			->with( $pageId )
			->willThrowException( new PageNotFoundException() );
		return $pageSelector;
	}

	private function newVfsContentProvider( array $pages ): ContentProvider {
		$content = vfsStream::setup(
			'content',
			null,
			[
				'web' => [ 'pages' => $pages ],
				'mail' => [],
				'shared' => [],
			]
		);
		$provider = new ContentProvider( [ 'content_path' => $content->url() ] );
		return $provider;
	}

	private function setDefaultSkin( FunFunFactory $factory, string $skinName ): void {
		$factory->setCampaignConfigurationLoader(
			new OverridingCampaignConfigurationLoader(
				$factory->getCampaignConfigurationLoader(),
				[ 'skins' => [ 'default_bucket' => $skinName ] ]
			)
		);
	}
}
