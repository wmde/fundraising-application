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
		$this->modifyEnvironment( function ( FunFunFactory $factory ): void {
			$factory->disableDoctrineSubscribers();
			$this->createThreeComments( $factory->getEntityManager() );
		} );
		$client = $this->createClient();

		$crawler = $client->request( 'GET', '/list-comments.html' );

		$this->assertCount(
			1,
			$crawler->filter( 'h2:contains("Die 3 neuesten Spenderkommentare")' )
		);
	}

	private function createThreeComments( EntityManager $entityManager ): void {
		$this->persistFirstComment( $entityManager );
		$this->persistSecondComment( $entityManager );
		$this->persistMaliciousComment( $entityManager );
		$entityManager->flush();
	}

	private function persistFirstComment( EntityManager $entityManager ): void {
		$firstDonation = new Donation();
		$firstDonation->setPublicRecord( 'First name' );
		$firstDonation->setComment( 'First comment' );
		$firstDonation->setAmount( '100.42' );
		$firstDonation->setCreationTime( new DateTime( '1984-01-01' ) );
		$firstDonation->setIsPublic( true );
		$firstDonation->setPaymentId( 1 );
		$entityManager->persist( $firstDonation );
	}

	private function persistSecondComment( EntityManager $entityManager ): void {
		$secondDonation = new Donation();
		$secondDonation->setPublicRecord( 'Second name' );
		$secondDonation->setComment( 'Second comment' );
		$secondDonation->setAmount( '9001' );
		$secondDonation->setCreationTime( new DateTime( '1984-02-02' ) );
		$secondDonation->setIsPublic( true );
		$secondDonation->setPaymentId( 2 );
		$entityManager->persist( $secondDonation );
	}

	private function persistMaliciousComment( EntityManager $entityManager ): void {
		$maliciousDonation = new Donation();
		$maliciousDonation->setPublicRecord( 'Third name & company' );
		$maliciousDonation->setComment( 'Third <script> comment' );
		$maliciousDonation->setAmount( '9001' );
		$maliciousDonation->setCreationTime( new DateTime( '1984-03-03' ) );
		$maliciousDonation->setIsPublic( true );
		$maliciousDonation->setPaymentId( 3 );
		$entityManager->persist( $maliciousDonation );
	}

	public function testWhenAreComments_theyAreInTheHtml(): void {
		$this->modifyEnvironment( function ( FunFunFactory $factory ): void {
			$factory->disableDoctrineSubscribers();
			$this->createThreeComments( $factory->getEntityManager() );
		} );
		$client = $this->createClient();

		$client->request( 'GET', '/list-comments.html' );

		// TODO Restructure template to use elements and classes.
		// Then we can use $crawler instead of searching strings

		$this->assertStringContainsString(
			'100,42&euro; von First name am',
			$client->getResponse()->getContent()
		);

		$this->assertStringContainsString(
			'First comment',
			$client->getResponse()->getContent()
		);

		$this->assertStringContainsString(
			'9.001,00&euro; von Second name am',
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
	}

	public function testCommentsGetEscaped(): void {
		$this->modifyEnvironment( function ( FunFunFactory $factory ): void {
			$factory->disableDoctrineSubscribers();
			$this->createThreeComments( $factory->getEntityManager() );
		} );
		$client = $this->createClient();

		$client->request( 'GET', '/list-comments.html' );

		$this->assertStringContainsString(
			'Third &lt;script&gt; comment',
			$client->getResponse()->getContent()
		);
	}

	public function testGivenLimitAndPageTwo_limitNumberOfCommentsAreSkipped(): void {
		$this->modifyEnvironment( function ( FunFunFactory $factory ): void {
			$factory->disableDoctrineSubscribers();
			$this->createThreeComments( $factory->getEntityManager() );
		} );
		$client = $this->createClient();

		$client->request( 'GET', '/list-comments.json?n=2&page=2' );

		$this->assertStringContainsString( 'First', $client->getResponse()->getContent() );
		$this->assertStringNotContainsString( 'Second', $client->getResponse()->getContent() );
		$this->assertStringNotContainsString( 'Third', $client->getResponse()->getContent() );
	}

}
