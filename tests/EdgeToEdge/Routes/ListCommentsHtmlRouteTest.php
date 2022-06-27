<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\Tests\Data\CommentsForTesting;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Donation\ListCommentsController
 */
class ListCommentsHtmlRouteTest extends WebRouteTestCase {

	public function testWhenThereAreNoComments_rssFeedIsEmpty(): void {
		$client = $this->createClient();
		$crawler = $client->request( 'GET', '/list-comments.html' );

		$this->assertTrue( $client->getResponse()->isOk(), 'request is successful' );

		$this->assertCount(
			1,
			// TODO Change template to use class instead of element
			$crawler->filter( 'h2:contains("Die 0 neuesten Spenderkommentare")' )
		);
	}

	public function testWhenAreThreeComments_listSizeIsShownAsThree(): void {
		$client = $this->createClient();
		$this->createThreeComments();

		$crawler = $client->request( 'GET', '/list-comments.html' );

		$this->assertCount(
			1,
			$crawler->filter( 'h2:contains("Die 3 neuesten Spenderkommentare")' )
		);
	}

	private function createThreeComments(): void {
		$factory = $this->getFactory();
		$factory->disableDoctrineSubscribers();
		$em = $factory->getEntityManager();
		CommentsForTesting::persistFirstComment( $em );
		CommentsForTesting::persistSecondComment( $em );
		CommentsForTesting::persistEvilComment( $em );
		$em->flush();
	}

	public function testWhenAreComments_theyAreInTheHtml(): void {
		$client = $this->createClient();
		$this->createThreeComments();

		$client->request( 'GET', '/list-comments.html' );

		// TODO Restructure template to use elements and classes.
		// Then we can use $crawler instead of searching strings

		$content = $client->getResponse()->getContent();
		$this->assertStringContainsString( '100,42&euro; von First name am', $content );
		$this->assertStringContainsString( 'First comment', $content );
		$this->assertStringContainsString( '9.001,00&euro; von Second name am', $content );
		$this->assertStringContainsString( 'Second comment', $content );
		$this->assertStringContainsString( 'name &amp; company', $content );
	}

	public function testCommentsGetEscaped(): void {
		$client = $this->createClient();
		$this->createThreeComments();

		$client->request( 'GET', '/list-comments.html' );

		$this->assertStringContainsString(
			'Third &lt;script&gt; comment',
			$client->getResponse()->getContent()
		);
	}

	public function testGivenLimitAndPageTwo_limitNumberOfCommentsAreSkipped(): void {
		$client = $this->createClient();
		$this->createThreeComments();

		$client->request( 'GET', '/list-comments.json?n=2&page=2' );

		$this->assertStringContainsString( 'First', $client->getResponse()->getContent() );
		$this->assertStringNotContainsString( 'Second', $client->getResponse()->getContent() );
		$this->assertStringNotContainsString( 'Third', $client->getResponse()->getContent() );
	}

}
