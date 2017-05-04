<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use DateTime;
use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ListCommentsHtmlRouteTest extends WebRouteTestCase {

	public function testWhenThereAreNoComments_rssFeedIsEmpty() {
		$client = $this->createClient();
		$crawler = $client->request( 'GET', '/list-comments.html' );

		$this->assertTrue( $client->getResponse()->isOk(), 'request is successful' );

		$this->assertCount(
			1,
			// TODO Change template to use class instead of element
			$crawler->filter( 'h2:contains("Die 0 neuesten Spenderkommentare")' )
		);
	}

	public function testWhenAreThreeComments_listSizeIsShownAsThree() {
		$client = $this->createClient( [], function ( FunFunFactory $factory ) {
			$this->createThreeComments( $factory->getEntityManager() );
		} );

		$crawler = $client->request( 'GET', '/list-comments.html' );

		$this->assertCount(
			1,
			$crawler->filter( 'h2:contains("Die 3 neuesten Spenderkommentare")' )
		);
	}

	private function createThreeComments( EntityManager $entityManager ): void {
		$this->persistFirstComment( $entityManager );
		$this->persistSecondComment( $entityManager );
		$this->persistEvilComment( $entityManager );
		$entityManager->flush();
	}

	private function persistFirstComment( EntityManager $entityManager ) {
		$firstDonation = new Donation();
		$firstDonation->setPublicRecord( 'First name' );
		$firstDonation->setComment( 'First comment' );
		$firstDonation->setAmount( '100.42' );
		$firstDonation->setCreationTime( new DateTime( '1984-01-01' ) );
		$firstDonation->setIsPublic( true );
		$entityManager->persist( $firstDonation );
	}

	private function persistSecondComment( EntityManager $entityManager ) {
		$secondDonation = new Donation();
		$secondDonation->setPublicRecord( 'Second name' );
		$secondDonation->setComment( 'Second comment' );
		$secondDonation->setAmount( '9001' );
		$secondDonation->setCreationTime( new DateTime( '1984-02-02' ) );
		$secondDonation->setIsPublic( true );
		$entityManager->persist( $secondDonation );
	}

	private function persistEvilComment( EntityManager $entityManager ) {
		$secondDonation = new Donation();
		$secondDonation->setPublicRecord( 'Third name & company' );
		$secondDonation->setComment( 'Third <script> comment' );
		$secondDonation->setAmount( '9001' );
		$secondDonation->setCreationTime( new DateTime( '1984-03-03' ) );
		$secondDonation->setIsPublic( true );
		$entityManager->persist( $secondDonation );
	}

	public function testWhenAreComments_theyAreInTheHtml() {
		$client = $this->createClient( [], function ( FunFunFactory $factory ) {
			$this->createThreeComments( $factory->getEntityManager() );
		} );

		$client->request( 'GET', '/list-comments.html' );

		// TODO Restructure template to use elements and classes.
		// Then we can use $crawler instead of searching strings

		$this->assertContains(
			'100,42&euro; von First name am',
			$client->getResponse()->getContent()
		);

		$this->assertContains(
			'First comment',
			$client->getResponse()->getContent()
		);

		$this->assertContains(
			'9.001,00&euro; von Second name am',
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
	}

	public function testCommentsGetEscaped() {
		$client = $this->createClient( [], function ( FunFunFactory $factory ) {
			$this->createThreeComments( $factory->getEntityManager() );
		} );

		$client->request( 'GET', '/list-comments.html' );

		$this->assertContains(
			'Third &lt;script&gt; comment',
			$client->getResponse()->getContent()
		);
	}

	public function testGivenLimitAndPageTwo_limitNumberOfCommentsAreSkipped() {
		$client = $this->createClient( [], function ( FunFunFactory $factory ) {
			$this->createThreeComments( $factory->getEntityManager() );
		} );

		$client->request( 'GET', '/list-comments.json?n=2&page=2' );

		$this->assertContains( 'First', $client->getResponse()->getContent() );
		$this->assertNotContains( 'Second', $client->getResponse()->getContent() );
		$this->assertNotContains( 'Third', $client->getResponse()->getContent() );
	}

}
