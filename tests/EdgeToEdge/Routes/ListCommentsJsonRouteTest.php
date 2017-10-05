<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use DateTime;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
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
		$client = $this->createClient( [], function( FunFunFactory $factory ): void {
			$this->persistFirstComment( $factory->getEntityManager() );
			$this->persistSecondComment( $factory->getEntityManager() );
			$factory->getEntityManager()->flush();
		} );

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
			'betrag' => 100,
			'spender' => 'First name',
			'kommentar' => 'First comment',
			'datum' => 'Sun, 01 Jan 1984 00:00:00 +0100',
			'id' => 1,
		];
	}

	private function getSecondCommentAsArray(): array {
		return [
			'betrag' => 200,
			'spender' => 'Second name',
			'kommentar' => 'Second comment',
			'datum' => 'Thu, 02 Feb 1984 00:00:00 +0100',
			'id' => 2,
		];
	}

	private function persistFirstComment( EntityManager $entityManager ): void {
		$firstDonation = new Donation();
		$firstDonation->setPublicRecord( 'First name' );
		$firstDonation->setComment( 'First comment' );
		$firstDonation->setAmount( '100' );
		$firstDonation->setCreationTime( new DateTime( '1984-01-01' ) );
		$firstDonation->setIsPublic( true );
		$entityManager->persist( $firstDonation );
	}

	private function persistSecondComment( EntityManager $entityManager ): void {
		$secondDonation = new Donation();
		$secondDonation->setPublicRecord( 'Second name' );
		$secondDonation->setComment( 'Second comment' );
		$secondDonation->setAmount( '200' );
		$secondDonation->setCreationTime( new DateTime( '1984-02-02' ) );
		$secondDonation->setIsPublic( true );
		$entityManager->persist( $secondDonation );
	}

	public function testGivenLimitSmallerThanCommentCount_onlySoManyCommentsAreShown(): void {
		$client = $this->createClient( [], function( FunFunFactory $factory ): void {
			$this->persistFirstComment( $factory->getEntityManager() );
			$this->persistSecondComment( $factory->getEntityManager() );
			$factory->getEntityManager()->flush();
		} );

		$client->request( 'GET', '/list-comments.json?n=1' );

		$this->assertJsonSuccessResponse(
			[
				$this->getSecondCommentAsArray()
			],
			$client->getResponse()
		);
	}

	public function testGivenJsonpCallback_jsonIsWrappedInCallback(): void {
		$client = $this->createClient( [], function( FunFunFactory $factory ): void {
			$this->persistFirstComment( $factory->getEntityManager() );
			$this->persistSecondComment( $factory->getEntityManager() );
			$factory->getEntityManager()->flush();
		} );

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
		$client = $this->createClient( [], function( FunFunFactory $factory ): void {
			$this->persistFirstComment( $factory->getEntityManager() );
			$this->persistSecondComment( $factory->getEntityManager() );
			$factory->getEntityManager()->flush();
		} );

		$client->request( 'GET', '/list-comments.json?n=1&page=2' );

		$this->assertJsonSuccessResponse(
			[
				$this->getFirstCommentAsArray()
			],
			$client->getResponse()
		);
	}

}
