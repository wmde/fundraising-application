<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use DateTime;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\System\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ListCommentsJsonRouteTest extends WebRouteTestCase {

	public function setUp() {
		date_default_timezone_set( 'Europe/Berlin' );
	}

	public function testWhenThereAreNoComments_emptyArrayIsShown() {
		$client = $this->createClient();
		$client->request( 'GET', '/list-comments.json?n=10' );

		$this->assertJsonSuccessResponse(
			[],
			$client->getResponse()
		);
	}

	public function testRouteShowsComments() {
		$client = $this->createClient( [], function( FunFunFactory $factory ) {
			$this->persistFirstComment( $factory->getEntityManager() );
			$this->persistSecondComment( $factory->getEntityManager() );
			$factory->getEntityManager()->flush();
		} );

		$client->request( 'GET', '/list-comments.json?n=10' );

		$this->assertJsonSuccessResponse(
			[
				[
					'betrag' => 200,
					'spender' => 'Second name',
					'kommentar' => 'Second comment',
					'datum' => 'Thu, 02 Feb 1984 00:00:00 +0100',
					'id' => 2,
				],
				[
					'betrag' => 100,
					'spender' => 'First name',
					'kommentar' => 'First comment',
					'datum' => 'Sun, 01 Jan 1984 00:00:00 +0100',
					'id' => 1,
				],
			],
			$client->getResponse()
		);
	}

	private function persistFirstComment( EntityManager $entityManager ) {
		$firstDonation = new Donation();
		$firstDonation->setPublicRecord( 'First name' );
		$firstDonation->setComment( 'First comment' );
		$firstDonation->setAmount( '100' );
		$firstDonation->setDtNew( new DateTime( '1984-01-01' ) );
		$firstDonation->setIsPublic( true );
		$entityManager->persist( $firstDonation );
	}

	private function persistSecondComment( EntityManager $entityManager ) {
		$secondDonation = new Donation();
		$secondDonation->setPublicRecord( 'Second name' );
		$secondDonation->setComment( 'Second comment' );
		$secondDonation->setAmount( '200' );
		$secondDonation->setDtNew( new DateTime( '1984-02-02' ) );
		$secondDonation->setIsPublic( true );
		$entityManager->persist( $secondDonation );
	}

	public function testGivenLimitSmallerThanCommentCount_onlySoManyCommentsAreShown() {
		$client = $this->createClient( [], function( FunFunFactory $factory ) {
			$this->persistFirstComment( $factory->getEntityManager() );
			$this->persistSecondComment( $factory->getEntityManager() );
			$factory->getEntityManager()->flush();
		} );

		$client->request( 'GET', '/list-comments.json?n=1' );

		$this->assertJsonSuccessResponse(
			[
				[
					'betrag' => 200,
					'spender' => 'Second name',
					'kommentar' => 'Second comment',
					'datum' => 'Thu, 02 Feb 1984 00:00:00 +0100',
					'id' => 2,
				]
			],
			$client->getResponse()
		);
	}

	public function testGivenJsonpCallback_jsonIsWrappedInCallback() {
		$client = $this->createClient( [], function( FunFunFactory $factory ) {
			$this->persistFirstComment( $factory->getEntityManager() );
			$this->persistSecondComment( $factory->getEntityManager() );
			$factory->getEntityManager()->flush();
		} );

		$client->request( 'GET', '/list-comments.json?n=1&f=kittens' );

		$this->assertJsonpSuccessResponse(
			[
				[
					'betrag' => 200,
					'spender' => 'Second name',
					'kommentar' => 'Second comment',
					'datum' => 'Thu, 02 Feb 1984 00:00:00 +0100',
					'id' => 2,
				]
			],
			'kittens',
			$client->getResponse()
		);
	}

	private function assertJsonpSuccessResponse( $expectedJson, string $expectedCallback, Response $response ) {
		$this->assertSame(
			'/**/' . $expectedCallback . '(' . json_encode( $expectedJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . ');',
			$response->getContent()
		);
	}

}
