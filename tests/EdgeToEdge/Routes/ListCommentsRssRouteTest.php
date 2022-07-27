<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use DateTime;
use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineEntities\Donation;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Donation\ListCommentsController
 */
class ListCommentsRssRouteTest extends WebRouteTestCase {

	public function testWhenThereAreNoComments_rssFeedIsEmpty(): void {
		$this->markTestIncomplete( "This will be fixed when we set this up with donations + payment fixtures" );
		$client = $this->createClient();
		$client->request( 'GET', '/list-comments.rss' );

		$this->assertTrue( $client->getResponse()->isSuccessful(), 'request is successful' );

		$this->assertEquals(
			file_get_contents( __DIR__ . '/../../Data/files/emptyCommentList.rss' ),
			$client->getResponse()->getContent()
		);
	}

	public function testWhenAreComments_theyAreInTheRss(): void {
		$this->markTestIncomplete( "This will be fixed when we set this up with donations + payment fixtures" );
		$this->modifyEnvironment( function ( FunFunFactory $factory ): void {
			$factory->disableDoctrineSubscribers();
			$this->persistFirstComment( $factory->getEntityManager() );
			$this->persistSecondComment( $factory->getEntityManager() );
			$this->persistEvilComment( $factory->getEntityManager() );
			$factory->getEntityManager()->flush();
		} );
		$client = $this->createClient();

		$client->request( 'GET', '/list-comments.rss' );

		$this->assertTrue( $client->getResponse()->isSuccessful(), 'request is successful' );

		$this->assertStringContainsString(
			'100,42 Euro von First name',
			$client->getResponse()->getContent()
		);

		$this->assertStringContainsString(
			'First comment',
			$client->getResponse()->getContent()
		);

		$this->assertStringContainsString(
			'9.001,00 Euro von Second name',
			$client->getResponse()->getContent()
		);

		$this->assertStringContainsString(
			'Second comment',
			$client->getResponse()->getContent()
		);

		$this->assertStringContainsString(
			'name &amp; company',
			$client->getResponse()->getContent()
		);

		$this->assertStringContainsString(
			'Third &lt;script&gt; comment',
			$client->getResponse()->getContent()
		);
	}

	private function persistFirstComment( EntityManager $entityManager ): void {
		$firstDonation = new Donation();
		$firstDonation->setPublicRecord( 'First name' );
		$firstDonation->setComment( 'First comment' );
		$firstDonation->setAmount( '100.42' );
		$firstDonation->setCreationTime( new DateTime( '1984-01-01' ) );
		$firstDonation->setIsPublic( true );
		$entityManager->persist( $firstDonation );
	}

	private function persistSecondComment( EntityManager $entityManager ): void {
		$secondDonation = new Donation();
		$secondDonation->setPublicRecord( 'Second name' );
		$secondDonation->setComment( 'Second comment' );
		$secondDonation->setAmount( '9001' );
		$secondDonation->setCreationTime( new DateTime( '1984-02-02' ) );
		$secondDonation->setIsPublic( true );
		$entityManager->persist( $secondDonation );
	}

	private function persistEvilComment( EntityManager $entityManager ): void {
		$secondDonation = new Donation();
		$secondDonation->setPublicRecord( 'Third name & company' );
		$secondDonation->setComment( 'Third <script> comment' );
		$secondDonation->setAmount( '9001' );
		$secondDonation->setCreationTime( new DateTime( '1984-02-02' ) );
		$secondDonation->setIsPublic( true );
		$entityManager->persist( $secondDonation );
	}
}
