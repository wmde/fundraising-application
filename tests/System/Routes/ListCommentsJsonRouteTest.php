<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use DateTime;
use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Entities\Spenden;
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
		$firstSpenden = new Spenden();
		$firstSpenden->setName( 'First name' );
		$firstSpenden->setKommentar( 'First comment' );
		$firstSpenden->setBetrag( '100' );
		$firstSpenden->setDtNew( new DateTime( '1984-01-01' ) );
		$firstSpenden->setIsPublic( true );
		$entityManager->persist( $firstSpenden );
	}

	private function persistSecondComment( EntityManager $entityManager ) {
		$secondSpenden = new Spenden();
		$secondSpenden->setName( 'Second name' );
		$secondSpenden->setKommentar( 'Second comment' );
		$secondSpenden->setBetrag( '200' );
		$secondSpenden->setDtNew( new DateTime( '1984-02-02' ) );
		$secondSpenden->setIsPublic( true );
		$entityManager->persist( $secondSpenden );
	}

	public function testGivenLimitSmallerThanCommentCount_onlySoManyCommentsAreShown() {
		// TODO: sorting
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



}
