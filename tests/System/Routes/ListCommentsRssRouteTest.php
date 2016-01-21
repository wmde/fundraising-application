<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use DateTime;
use Doctrine\ORM\EntityManager;
use DOMDocument;
use DOMElement;
use WMDE\Fundraising\Entities\Spenden;
use WMDE\Fundraising\Frontend\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\System\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ListCommentsRssRouteTest extends WebRouteTestCase {

	public function setUp() {
		date_default_timezone_set( 'Europe/Berlin' );
	}

	public function testWhenThereAreNoComments_rssFeedIsEmpty() {
		$client = $this->createClient();
		$client->request( 'GET', '/list-comments.rss' );

		$this->assertTrue( $client->getResponse()->isSuccessful(), 'request is successful' );

		$this->assertEquals(
			TestEnvironment::getTestData( 'emptyCommentList.rss' ),
			$client->getResponse()->getContent()
		);
	}

	public function testWhenAreComments_theyAreInTheRss() {
		$client = $this->createClient( [], function( FunFunFactory $factory ) {
			$this->persistFirstComment( $factory->getEntityManager() );
			$this->persistSecondComment( $factory->getEntityManager() );
			$factory->getEntityManager()->flush();
		} );

		$client->request( 'GET', '/list-comments.rss' );

		$this->assertTrue( $client->getResponse()->isSuccessful(), 'request is successful' );

		$this->assertContains(
			'100,42 Euro von First name',
			$client->getResponse()->getContent()
		);

		$this->assertContains(
			'First comment',
			$client->getResponse()->getContent()
		);

		$this->assertContains(
			'9.001,00 Euro von Second name',
			$client->getResponse()->getContent()
		);

		$this->assertContains(
			'Second comment',
			$client->getResponse()->getContent()
		);
	}

	private function persistFirstComment( EntityManager $entityManager ) {
		$firstSpenden = new Spenden();
		$firstSpenden->setName( 'First name' );
		$firstSpenden->setKommentar( 'First comment' );
		$firstSpenden->setBetrag( '100.42' );
		$firstSpenden->setDtNew( new DateTime( '1984-01-01' ) );
		$firstSpenden->setIsPublic( true );
		$entityManager->persist( $firstSpenden );
	}

	private function persistSecondComment( EntityManager $entityManager ) {
		$secondSpenden = new Spenden();
		$secondSpenden->setName( 'Second name' );
		$secondSpenden->setKommentar( 'Second comment' );
		$secondSpenden->setBetrag( '9001' );
		$secondSpenden->setDtNew( new DateTime( '1984-02-02' ) );
		$secondSpenden->setIsPublic( true );
		$entityManager->persist( $secondSpenden );
	}

}
