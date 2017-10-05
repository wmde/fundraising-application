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

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DisplayPageRouteTest extends WebRouteTestCase {

	public function testWhenPageDoesNotExist_missingResponseIsReturnedAndHasHeaderAndFooter(): void {
		$client = $this->createClient(
			[],
			function ( FunFunFactory $factory ): void {
				$pageSelector = $this->createMock( PageSelector::class );
				$pageSelector
					->method( 'getPageId' )
					->with( 'kittens' )
					->willThrowException( new PageNotFoundException() );
				$factory->setContentPagePageSelector( $pageSelector );
			}
		);
		$client->request( 'GET', '/page/kittens' );

		$content = $client->getResponse()->getContent();

		$this->assertContains(
			'page_not_found',
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

	public function testWhenPageDoesNotExist_noUnescapedPageNameIsShown(): void {
		$client = $this->createClient(
			[],
			function ( FunFunFactory $factory ): void {
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

				$pageSelector = $this->createMock( PageSelector::class );
				$pageSelector
					->method( 'getPageId' )
					->willReturnArgument( 0 )
					->with( 'einhorns' )
					->willReturn( 'unicorns' );
				$factory->setContentPagePageSelector( $pageSelector );

				$content = vfsStream::setup( 'content', null, [
					'web' => [
						'pages' => [
							'unicorns.twig' => '<p>Rosa plüsch einhorns tanzen auf Regenbogen</p>',
						]
					],
					'mail' => [],
					'shared' => [],
				] );
				$provider = new ContentProvider( [
					'content_path' => $content->url()
				] );
				$factory->setContentProvider( $provider );

				$crawler = $client->request( 'GET', '/page/einhorns' );

				$this->assertCount( 1, $crawler->filter( 'body.page-unicorns' ) );
				$this->assertCount( 1, $crawler->filter( 'header:contains("page header")' ) );
				$this->assertCount( 1, $crawler->filter( 'main#main p:contains("Rosa plüsch einhorns tanzen auf Regenbogen")' ) );
				$this->assertCount( 1, $crawler->filter( 'main#main div.sandboxedcontent.unicorns' ) );
				$this->assertCount( 1, $crawler->filter( 'footer:contains("page footer")' ) );
				$this->assertCount( 1, $crawler->filter( 'div#notice-wrapper:contains("Y u no JavaScript!")' ) );
			}
		);
	}

	public function testWhenPageNameContainsSlash_404isReturned(): void {
		$client = $this->createClient( [], null, self::DISABLE_DEBUG );
		$client->request( 'GET', '/page/unicorns/of-doom' );

		$this->assert404( $client->getResponse() );
	}
}
