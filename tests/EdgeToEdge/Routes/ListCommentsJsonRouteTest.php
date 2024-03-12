<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Tests\Data\CommentsForTesting;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Donation\ListCommentsController
 */
class ListCommentsJsonRouteTest extends WebRouteTestCase {

	public function setUp(): void {
		date_default_timezone_set( 'Europe/Berlin' );
	}

	public function testWhenThereAreNoComments_emptyArrayIsShown(): void {
		$client = $this->createClient();
		$client->request( 'GET', '/list-comments.json?n=10' );

		$this->assertJsonSuccessResponse(
			[],
			$client->getResponse()
		);
	}

	public function testRouteShowsComments(): void {
		$client = $this->createClient();
		$this->givenComments();

		$client->request( 'GET', '/list-comments.json?n=10' );

		$this->assertJsonSuccessResponse(
			[
				$this->getSecondCommentAsArray(),
				$this->getFirstCommentAsArray()
			],
			$client->getResponse()
		);
	}

	private function getFirstCommentAsArray(): array {
		return [
			'betrag' => 100.42,
			'spender' => 'First name',
			'kommentar' => 'First comment',
			'datum' => 'Sun, 01 Jan 1984 00:00:00 +0100',
			'lokalisiertes_datum' => '01.01.1984 um 00:00 Uhr',
			'id' => 1,
		];
	}

	private function getSecondCommentAsArray(): array {
		return [
			'betrag' => 9001,
			'spender' => 'Second name',
			'kommentar' => 'Second comment',
			'datum' => 'Thu, 02 Feb 1984 00:00:00 +0100',
			'lokalisiertes_datum' => '02.02.1984 um 00:00 Uhr',
			'id' => 2,
		];
	}

	public function testGivenLimitSmallerThanCommentCount_onlySoManyCommentsAreShown(): void {
		$client = $this->createClient();
		$this->givenComments();

		$client->request( 'GET', '/list-comments.json?n=1' );

		$this->assertJsonSuccessResponse(
			[
				$this->getSecondCommentAsArray()
			],
			$client->getResponse()
		);
	}

	public function testGivenJsonpCallback_jsonIsWrappedInCallback(): void {
		$client = $this->createClient();
		$this->givenComments();

		$client->request( 'GET', '/list-comments.json?n=1&f=kittens' );

		$this->assertJsonpSuccessResponse(
			[
				$this->getSecondCommentAsArray()
			],
			'kittens',
			$client->getResponse()
		);
	}

	private function assertJsonpSuccessResponse( array $expectedJson, string $expectedCallback, Response $response ): void {
		$this->assertSame(
			'/**/' . $expectedCallback . '(' . json_encode( $expectedJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . ');',
			$response->getContent()
		);
	}

	public function testGivenLimitAndPageTwo_limitNumberOfCommentsAreSkipped(): void {
		$client = $this->createClient();
		$this->givenComments();

		$client->request( 'GET', '/list-comments.json?n=1&page=2' );

		$this->assertJsonSuccessResponse(
			[
				$this->getFirstCommentAsArray()
			],
			$client->getResponse()
		);
	}

	private function givenComments(): void {
		$factory = $this->getFactory();
		$em = $factory->getEntityManager();
		CommentsForTesting::persistFirstComment( $em );
		CommentsForTesting::persistSecondComment( $em );
		$em->flush();
	}

}
