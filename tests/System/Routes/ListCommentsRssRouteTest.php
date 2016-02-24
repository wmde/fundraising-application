<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use DateTime;
use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\System\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ListCommentsRssRouteTest extends WebRouteTestCase {

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
			$this->persistEvilComment( $factory->getEntityManager() );
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

		$this->assertContains(
			'name &amp; company',
			$client->getResponse()->getContent()
		);

		$this->assertContains(
			'Third &lt;script&gt; comment',
			$client->getResponse()->getContent()
		);
	}

	private function persistFirstComment( EntityManager $entityManager ) {
		$firstDonation = new Donation();
		$firstDonation->setPublicRecord( 'First name' );
		$firstDonation->setComment( 'First comment' );
		$firstDonation->setAmount( '100.42' );
		$firstDonation->setDtNew( new DateTime( '1984-01-01' ) );
		$firstDonation->setIsPublic( true );
		$entityManager->persist( $firstDonation );
	}

	private function persistSecondComment( EntityManager $entityManager ) {
		$secondDonation = new Donation();
		$secondDonation->setPublicRecord( 'Second name' );
		$secondDonation->setComment( 'Second comment' );
		$secondDonation->setAmount( '9001' );
		$secondDonation->setDtNew( new DateTime( '1984-02-02' ) );
		$secondDonation->setIsPublic( true );
		$entityManager->persist( $secondDonation );
	}

	private function persistEvilComment( EntityManager $entityManager ) {
		$secondDonation = new Donation();
		$secondDonation->setPublicRecord( 'Third name & company' );
		$secondDonation->setComment( 'Third <script> comment' );
		$secondDonation->setAmount( '9001' );
		$secondDonation->setDtNew( new DateTime( '1984-02-02' ) );
		$secondDonation->setIsPublic( true );
		$entityManager->persist( $secondDonation );
	}
}
