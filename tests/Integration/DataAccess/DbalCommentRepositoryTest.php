<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\Integration\DataAccess;

use DateTime;
use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\DataAccess\DbalCommentRepository;
use WMDE\Fundraising\Frontend\Domain\Comment;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @covers WMDE\Fundraising\Frontend\DataAccess\DbalCommentRepository
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DbalCommentRepositoryTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	public function setUp() {
		$this->entityManager = TestEnvironment::newInstance()->getFactory()->getEntityManager();
		parent::setUp();
	}

	private function getOrmRepository() {
		return $this->entityManager->getRepository( Donation::class );
	}

	public function testWhenThereAreNoComments_anEmptyListIsReturned() {
		$repository = new DbalCommentRepository( $this->getOrmRepository() );

		$this->assertEmpty( $repository->getPublicComments( 10 ) );
	}

	public function testWhenThereAreLessCommentsThanTheLimit_theyAreAllReturned() {
		$this->persistFirstComment();
		$this->persistSecondComment();
		$this->persistThirdComment();
		$this->entityManager->flush();

		$repository = new DbalCommentRepository( $this->getOrmRepository() );

		$this->assertEquals(
			[
				$this->getThirdComment( 3 ),
				$this->getSecondComment(),
				$this->getFirstComment(),
			],
			$repository->getPublicComments( 10 )
		);
	}

	public function testWhenThereAreMoreCommentsThanTheLimit_aLimitedNumberAreReturned() {
		$this->persistFirstComment();
		$this->persistSecondComment();
		$this->persistThirdComment();
		$this->entityManager->flush();

		$repository = new DbalCommentRepository( $this->getOrmRepository() );

		$this->assertEquals(
			[
				$this->getThirdComment( 3 ),
				$this->getSecondComment(),
			],
			$repository->getPublicComments( 2 )
		);
	}

	public function testOnlyPublicCommentsGetReturned() {
		$this->persistFirstComment();
		$this->persistSecondComment();
		$this->persistPrivateComment();
		$this->persistThirdComment();
		$this->entityManager->flush();

		$repository = new DbalCommentRepository( $this->getOrmRepository() );

		$this->assertEquals(
			[
				$this->getThirdComment( 4 ),
				$this->getSecondComment(),
				$this->getFirstComment(),
			],
			$repository->getPublicComments( 10 )
		);
	}

	public function testOnlyNonDeletedCommentsGetReturned() {
		$this->persistFirstComment();
		$this->persistSecondComment();
		$this->persistDeletedComment();
		$this->persistThirdComment();
		$this->entityManager->flush();

		$repository = new DbalCommentRepository( $this->getOrmRepository() );

		$this->assertEquals(
			[
				$this->getThirdComment( 4 ),
				$this->getSecondComment(),
				$this->getFirstComment(),
			],
			$repository->getPublicComments( 10 )
		);
	}

	private function persistFirstComment() {
		$firstDonation = new Donation();
		$firstDonation->setPublicRecord( 'First name' );
		$firstDonation->setComment( 'First comment' );
		$firstDonation->setAmount( '100' );
		$firstDonation->setDtNew( new DateTime( '1984-01-01' ) );
		$firstDonation->setIsPublic( true );
		$this->entityManager->persist( $firstDonation );
	}

	private function persistSecondComment() {
		$secondDonation = new Donation();
		$secondDonation->setPublicRecord( 'Second name' );
		$secondDonation->setComment( 'Second comment' );
		$secondDonation->setAmount( '200' );
		$secondDonation->setDtNew( new DateTime( '1984-02-02' ) );
		$secondDonation->setIsPublic( true );
		$this->entityManager->persist( $secondDonation );
	}

	private function persistThirdComment() {
		$thirdDonation = new Donation();
		$thirdDonation->setPublicRecord( 'Third name' );
		$thirdDonation->setComment( 'Third comment' );
		$thirdDonation->setAmount( '300' );
		$thirdDonation->setDtNew( new DateTime( '1984-03-03' ) );
		$thirdDonation->setIsPublic( true );
		$this->entityManager->persist( $thirdDonation );
	}

	private function persistPrivateComment() {
		$privateDonation = new Donation();
		$privateDonation->setPublicRecord( 'Private name' );
		$privateDonation->setComment( 'Private comment' );
		$privateDonation->setAmount( '1337' );
		$privateDonation->setDtNew( new DateTime( '1984-12-12' ) );
		$privateDonation->setIsPublic( false );
		$this->entityManager->persist( $privateDonation );
	}

	private function persistDeletedComment() {
		$deletedDonation = new Donation();
		$deletedDonation->setPublicRecord( 'Deleted name' );
		$deletedDonation->setComment( 'Deleted comment' );
		$deletedDonation->setAmount( '31337' );
		$deletedDonation->setDtNew( new DateTime( '1984-11-11' ) );
		$deletedDonation->setIsPublic( true );
		$deletedDonation->setDtDel( new DateTime( '2000-01-01' ) );
		$this->entityManager->persist( $deletedDonation );
	}

	private function getFirstComment() {
		return Comment::newInstance()
			->setAuthorName( 'First name' )
			->setCommentText( 'First comment' )
			->setDonationAmount( 100 )
			->setPostingTime( new \DateTime( '1984-01-01' ) )
			->setDonationId( 1 )
			->freeze();
	}


	private function getSecondComment() {
		return Comment::newInstance()
			->setAuthorName( 'Second name' )
			->setCommentText( 'Second comment' )
			->setDonationAmount( 200 )
			->setPostingTime( new \DateTime( '1984-02-02' ) )
			->setDonationId( 2 )
			->freeze();
	}

	private function getThirdComment( int $donationId ) {
		return Comment::newInstance()
			->setAuthorName( 'Third name' )
			->setCommentText( 'Third comment' )
			->setDonationAmount( 300 )
			->setPostingTime( new \DateTime( '1984-03-03' ) )
			->setDonationId( $donationId )
			->freeze();
	}


}
