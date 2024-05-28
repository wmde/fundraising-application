<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\Frontend\Tests\Data\CommentsForTesting;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Donation\ListCommentsController
 */
class ListCommentsRssRouteTest extends WebRouteTestCase {

	public function testWhenThereAreNoComments_rssFeedIsEmpty(): void {
		$client = $this->createClient();

		$client->request( 'GET', '/list-comments.rss' );

		$this->assertTrue( $client->getResponse()->isSuccessful(), 'request is successful' );
		$this->assertEquals(
			file_get_contents( __DIR__ . '/../../Data/files/emptyCommentList.rss' ),
			$client->getResponse()->getContent()
		);
	}

	public function testWhenAreComments_theyAreInTheRss(): void {
		$client = $this->createClient();
		$this->givenThreeComments();

		$client->request( 'GET', '/list-comments.rss' );

		$this->assertTrue( $client->getResponse()->isSuccessful(), 'request is successful' );
		$content = $client->getResponse()->getContent();

		$this->assertStringContainsString( '100,42 Euro von First name', $content ?: '' );
		$this->assertStringContainsString( 'First comment', $content ?: '' );
		$this->assertStringContainsString( '9.001,00 Euro von Second name', $content ?: '' );
		$this->assertStringContainsString( 'Second comment', $content ?: '' );
		$this->assertStringContainsString( 'name &amp; company', $content ?: '' );
		$this->assertStringContainsString( 'Third &lt;script&gt; comment', $content ?: '' );
	}

	private function givenThreeComments(): void {
		$factory = $this->getFactory();
		$em = $factory->getEntityManager();
		CommentsForTesting::persistFirstComment( $em );
		CommentsForTesting::persistSecondComment( $em );
		CommentsForTesting::persistEvilComment( $em );
		$em->flush();
	}
}
